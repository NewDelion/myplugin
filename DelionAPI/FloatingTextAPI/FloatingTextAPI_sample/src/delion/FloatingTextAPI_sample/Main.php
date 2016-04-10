<?php
namespace delion\FloatingTextAPI_sample;

use delion\FloatingTextAPI_sample\FloatingTextAPI;//必須(FloatingTextAPI.phpのnamespaceを編集してください。)

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;

class Main extends PluginBase implements Listener{
	function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->api = new FloatingTextAPI($this);//必須

		$this->counter_id = [];//プレイヤーカウンターのid一覧
	}

	function onPlayerJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();

		$this->api->initPlayer($player);//initPlayerの呼び出しは必須

		$title = 'プレイヤーの人数 => ' . count($this->getServer()->getOnlinePlayers());
		foreach($this->counter_id as $id){
			$result = $this->api->setTitle($id, $title);//カウンターの表示を更新
		}
	}
	function onPlayerQuit(PlayerQuitEvent $event){
		$title = 'プレイヤーの人数 => ' . (count($this->getServer()->getOnlinePlayers()) - 1);
		foreach($this->counter_id as $id){
			$result = $this->api->setTitle($id, $title);//カウンターの表示を更新
		}
	}

	function onCommand(CommandSender $sender, Command $command, $label, array $args){
		switch($command->getName()){
			case 'setclock':
				$pos = new Vector3($sender->x, $sender->y, $sender->z);
				$text_id = 'clock' . (string)mt_rand(1000,10000);//idの重複を避けるためにランダムな文字列をくっつける
				$this->api->addText($text_id, $pos, '', '');//テキストを表示
				$this->getServer()->getScheduler()->scheduleRepeatingTask(new Clock($this, $text_id), 20);
				break;
			case 'setplcount':
				$pos = new Vector3($sender->x, $sender->y, $sender->z);
				$text_id = 'counter' . (string)mt_rand(1000,10000);//idの重複を避けるためにランダムな文字列をくっつける
				$this->api->addText($text_id, $pos, 'プレイヤーの人数 => ' . count($this->getServer()->getOnlinePlayers()), '');//テキストを表示
				$this->counter_id[] = $text_id;//idを一覧に登録
				break;
			case 'removeall':
				$this->getServer()->getScheduler()->cancelTasks($this);//クロックをとめる
				$this->api->removeAllText();//全てのテキストを削除
				$this->counter_id = [];//カウンター一覧を初期化
				break;
		}
		return true;
	}
}

class Clock extends \pocketmine\scheduler\PluginTask{//CallbackTaskが無いソースのため
	function __construct(PluginBase $owner, $text_id){
		$this->owner = $owner;
		$this->text_id = $text_id;
	}
	function onRun($tick){
		//タイトル(日時)を更新
		//今回はテキストの部分は使用しない
		$title = TextFormat::YELLOW . date('Y/m/d H:i:s');
		if(!$this->owner->api->setTitle($this->text_id, $title)){//指定したテキストが見つからなかったらfalseを返す
			$this->owner->getServer()->getScheduler()->cancelTask($this->getTaskId());
		}
	}
}
