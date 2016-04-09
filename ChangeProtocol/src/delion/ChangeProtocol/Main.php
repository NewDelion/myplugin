<?php
namespace delion\ChangeProtocol;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\network\protocol\Info;

class Main extends PluginBase implements Listener{
    function onEnable(){
        if (!file_exists($this->getDataFolder())) @mkdir($this->getDataFolder(), 0740, true);
        $config = new Config($this->getDataFolder() . 'config.yml', Config::YAML, [ 'protocol' => 47 ]);
        $protocol = $config->get('protocol');
        if(Info::CURRENT_PROTOCOL == $protocol){
            $this->getLogger()->info(TextFormat::LIGHT_PURPLE . 'プロトコルに変更はありません。');
            $this->getServer()->getPluginManager()->registerEvents($this, $this);
            return;
        }
        if(substr(\pocketmine\PATH, 0, 7) === "phar://"){
            $file = file_get_contents(\pocketmine\PATH . 'src/pocketmine/network/protocol/Info.php');
            $pattern = '/CURRENT_PROTOCOL = [0-9]+;/';
            $after = "CURRENT_PROTOCOL = $pattern;";
            $file = preg_replace($pattern, $after, $file);
            $phar = new \Phar(\pocketmine\DATA . 'PocketMine-MP.phar');
            $phar->addFromString('src\pocketmine\network\protocol\Info.php', $file);
        }
        else{
            $file = file_get_contents(\pocketmine\DATA . 'src/pocketmine/network/protocol/Info.php');
            $pattern = '/CURRENT_PROTOCOL = [0-9]+;/';
            $after = "CURRENT_PROTOCOL = $pattern;";
            $file = preg_replace($pattern, $after, $file);
            file_put_contents(\pocketmine\DATA . 'src/pocketmine/network/protocol/Info.php', $file);
        }
        $this->getLogger()->info(TextFormat::AQUA . 'プロトコルを編集しました。');
        $this->getServer()->shutdown();
    }
    
    function onDataPacketReceive(DataPacketReceiveEvent $event){
        $packet = $event->getPacket();
        if($packet instanceof \pocketmine\network\protocl\LoginPacket){
            if($packet->protocol1 > Info::CURRENT_PROTOCOL){
                $this->getLogger()->info(TextFormat::LIGHT_PURPLE . '最新のプロトコルナンバーを検出！！');
                $this->getLogger()->info(TextFormat::LIGHT_PURPLE . 'プロトコルナンバー：' . $packet->protocol1);
            }
        }
    }
}
