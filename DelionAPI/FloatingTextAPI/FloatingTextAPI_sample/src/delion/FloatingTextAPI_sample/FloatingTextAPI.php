<?php
namespace delion\FloatingTextAPI_sample;//要編集

use pocketmine\plugin\PluginBase;
use pocketmine\entity\Entity;

class FloatingTextAPI{

	public $owner;
	private $text_list = [];

	public function __construct(PluginBase $owner){
		$this->owner = $owner;
	}

	public function initPlayer($player){
		foreach(array_keys($this->text_list) as $id)
			$this->spawnText($id, $player);
	}

	/**
	 * @param string $text_id
	 *
	 * @return bool
	 */
	public function existsText($text_id){
		return isset($this->text_list[$text_id]);
	}

	/**
	 * @param string $text_id
	 * @param string $name
	 *
	 * @return bool
	 */
	public function canShow($text_id, $name){
		if(!$this->existsText($text_id)) return false;
		if(!isset($this->text_list[$text_id]['show'])) return true;
		foreach($this->text_list[$text_id]['show'] as $n)
			if($n == $name)
				return true;
		return false;
	}

	/**
	 * @param string        $text_id
	 * @param Vector3       $position
	 * @param string        $title
	 * @param string        $text
	 * @param array(string) $show
	 *
	 * @return bool
	 */
	public function addText($text_id, $position, $title, $text, $show = false){
		if($this->existsText($text_id))
			return false;
		$this->text_list[$text_id]['position'] = $position;
		$this->text_list[$text_id]['title'] = $title;
		$this->text_list[$text_id]['text'] = $text;
		if($show !== false)
			$this->text_list[$text_id]['show'] = $show;
		$this->spawnText($text_id);
		return true;
	}

	/**
	 * @param string $text_id
	 * @param string $title
	 *
	 * @return bool
	 */
	public function setTitle($text_id, $title){
		if(!$this->existsText($text_id)) return false;
		$this->text_list[$text_id]['title'] = $title;
		$this->changeText($text_id);
		return true;
	}

	/**
	 * @param string $text_id
	 * @param string $text
	 *
	 * @return bool
	 */
	public function setText($text_id, $text){
		if(!$this->existsText($text_id)) return false;
		$this->text_list[$text_id]['text'] = $text;
		$this->changeText($text_id);
		return true;
	}

	/**
	 * @param string $text_id
	 * @param Vector3 $position
	 *
	 * @return bool
	 */
	public function setPosition($text_id, $position){
		if(!$this->existsText($text_id)) return false;
		$this->text_list[$text_id]['position'] = $position;
		$this->moveText($text_id);
		return true;
	}

	/**
	 * @param string $text_id
	 *
	 * @return bool
	 */
	public function removeText($text_id){
		if(!$this->existsText($text_id)) return false;
		$this->despawnText($text_id);
		unset($this->text_list[$text_id]);
		return true;
	}

	public function removeAllText(){
		foreach(array_keys($this->text_list) as $id)
			$this->despawnText($id);
		$this->text_list = [];
	}


	private function spawnText($text_id, $player = false){
		$pk = new \pocketmine\network\protocol\AddEntityPacket();
		if(!isset($this->text_list[$text_id]['eid']))
			$this->text_list[$text_id]['eid'] = mt_rand(10000, 100000);
		$pk->eid = $this->text_list[$text_id]['eid'];
		$pk->type = 64;
		$pk->x = $this->text_list[$text_id]['position']->x;
		$pk->y = $this->text_list[$text_id]['position']->y;
		$pk->z = $this->text_list[$text_id]['position']->z;
		$text = $this->text_list[$text_id]['title'] . ($this->text_list[$text_id]['text'] !== "" ? "\n" . $this->text_list[$text_id]['text'] : "");
		$pk->metadata = [
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, 1 << Entity::DATA_FLAG_INVISIBLE],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $text],
			Entity::DATA_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
			Entity::DATA_NO_AI => [Entity::DATA_TYPE_BYTE, 1]
		];
		if($player !== false && $this->canShow($text_id, $player->getName()))
			$player->dataPacket($pk);
		else if(isset($this->text_list[$text_id]['show'])){
			foreach($this->text_list[$text_id]['show'] as $name)
				if(($pl = $this->owner->getServer()->getPlayer($name)) != null)
					$pl->dataPacket($pk);
		}
		else
			$this->owner->getServer()->broadcastPacket($this->owner->getServer()->getOnlinePlayers(), $pk);
	}
	private function despawnText($text_id, $player = false){
		$pk = new \pocketmine\network\protocol\RemoveEntityPacket();
		$pk->eid = $this->text_list[$text_id]['eid'];
		if($player !== false && $this->canShow($text_id, $player->getName()))
			$player->dataPacket($pk);
		else if(isset($this->text_list[$text_id]['show'])){
			foreach($this->text_list[$text_id]['show'] as $name)
				if(($pl = $this->owner->getServer()->getPlayer($name)) != null)
					$pl->dataPacket($pk);
		}
		else
			$this->owner->getServer()->broadcastPacket($this->owner->getServer()->getOnlinePlayers(), $pk);
	}
	private function changeText($text_id, $player = false){
		$pk = new \pocketmine\network\protocol\SetEntityDataPacket();
		$pk->eid = $this->text_list[$text_id]['eid'];
		$text = $this->text_list[$text_id]['title'] . ($this->text_list[$text_id]['text'] !== "" ? "\n" . $this->text_list[$text_id]['text'] : "");
		$pk->metadata = [
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, 1 << Entity::DATA_FLAG_INVISIBLE],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $text],
			Entity::DATA_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
			Entity::DATA_NO_AI => [Entity::DATA_TYPE_BYTE, 1]
		];
		if($player !== false && $this->canShow($text_id, $player->getName()))
			$player->dataPacket($pk);
		else if(isset($this->text_list[$text_id]['show'])){
			foreach($this->text_list[$text_id]['show'] as $name)
				if(($pl = $this->owner->getServer()->getPlayer($name)) != null)
					$pl->dataPacket($pk);
		}
		else
			$this->owner->getServer()->broadcastPacket($this->owner->getServer()->getOnlinePlayers(), $pk);
	}
	private function moveText($text_id, $player = false){
		$pk = new \pocketmine\network\protocol\MoveEntityPacket();
		$pk->entities = [$this->text_list[$text_id]['eid'], $this->text_list[$text_id]['position']->x, $this->text_list[$text_id]['position']->y, $this->text_list[$text_id]['position']->z, 0, 0, 0];
		if($player !== false && $this->canShow($text_id, $player->getName()))
			$player->dataPacket($pk);
		else if(isset($this->text_list[$text_id]['show'])){
			foreach($this->text_list[$text_id]['show'] as $name)
				if(($pl = $this->owner->getServer()->getPlayer($name)) != null)
					$pl->dataPacket($pk);
		}
		else
			$this->owner->getServer()->broadcastPacket($this->owner->getServer()->getOnlinePlayers(), $pk);
	}
}
