<?php
namespace delion\no_title;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\scheduler\PluginTask;
use pocketmine\level\particle\DustParticle;
use pocketmine\math\Vector3;

class Main extends PluginBase implements Listener{
    function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $htis);
    }
    function onPlayerInteract(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        $pos = $event->getBlock()->add(0.5, 3.5, 0.5);
        $points = [];
        for($deg = 0; $deg < 360; $deg++){
            $rad = deg2rad($deg);
            $p = $pos->add(cos($rad), 0, sin($rad));
            $points[] = [ 'position' => $p, 'r' => 255, 'g' => 0, 'b' => 0 ];
        }
        for($deg = 0; $deg < 360; $deg++){
            $rad = deg2rad($deg);
            $p = $pos->add(cos($rad), sin($rad), 0);
            $points[] = [ 'position' => $p, 'r' => 255, 'g' => 0, 'b' => 0 ];
        }
        for($deg = 0; $deg < 360; $deg++){
            $rad = deg2rad($deg);
            $p = $pos->add(0, cos($rad), sin($rad));
            $points[] = [ 'position' => $p, 'r' => 255, 'g' => 0, 'b' => 0 ];
        }
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new Magic($this, $player->getLevel(), $points), 2);
    }
}

class Magic extends PluginTask{
    function __construct(PluginBase $owner, $level, $points){
        $this->owner = $owner;
        $this->level = $level;
        $this->points = $points;
    }
    function onRun($tick){
        $point = array_shift($this->points);
        $dust = new DustParticle($point['position'], $point['r'], $point['g'], $point['b']);
        $this->level->addParticle($dust);
        if(count($this->points) == 0)
            $this->owner->getServer()->getScheduler()->cancelTask($this->getTaskId());
    }
}
