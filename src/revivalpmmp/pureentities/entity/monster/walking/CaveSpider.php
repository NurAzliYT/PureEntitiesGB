<?php

namespace revivalpmmp\pureentities\entity\monster\walking;

use revivalpmmp\pureentities\entity\monster\WalkingMonster;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;

class CaveSpider extends WalkingMonster{
    const NETWORK_ID = 40;

    public $width = 0.9;
    public $height = 0.8;

    public function getSpeed() : float{
        return 1.3;
    }

    public function initEntity(){
        parent::initEntity();
        $this->setDamage([0, 2, 3, 3]);
    }

    public function getName(){
        return "CaveSpider";
    }

    public function attackEntity(Entity $player){
        if($this->attackDelay > 10 && $this->distanceSquared($player) < 1.32){
            $this->attackDelay = 0;
            $ev = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getDamage());
            $player->attack($ev->getFinalDamage(), $ev);
        }
    }

    public function getDrops(){
        $drops = [];
        array_push($drops, Item::get(Item::STRING, 0, mt_rand(0, 2)));
        switch (mt_rand(0, 2)) {
          case 0:
            array_push($drops, Item::get(Item::SPIDER_EYE, 0, 1));
            break;
        }
        return $drops;
    }

    public function getMaxHealth() {
        return 12;
    }

}
