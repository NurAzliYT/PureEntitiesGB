<?php

namespace revivalpmmp\pureentities\entity\monster\walking;

use revivalpmmp\pureentities\entity\monster\WalkingMonster;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\entity\Creature;
use pocketmine\Player;

class IronGolem extends WalkingMonster{
    const NETWORK_ID = 20;

    public $width = 1.9;
    public $height = 2.1;

    public function getSpeed() : float{
        return 0.8;
    }

    public function initEntity(){
        parent::initEntity();

        $this->setFriendly(true);
        $this->setDamage([0, 21, 21, 21]);
        $this->setMinDamage([0, 7, 7, 7]);
    }

    public function getName(){
        return "IronGolem";
    }

    public function attackEntity(Entity $player){
        if($this->attackDelay > 10 && $this->distanceSquared($player) < 4){
            $this->attackDelay = 0;

            $ev = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getDamage());
            $player->attack($ev->getFinalDamage(), $ev);
            $player->setMotion(new Vector3(0, 0.7, 0));
        }
    }

    public function targetOption(Creature $creature, float $distance) : bool{
        if(!($creature instanceof Player)){
            return $creature->isAlive() && $distance <= 60;
        }
        return false;
    }

    public function getDrops(){
        $drops = [];
        array_push($drops, Item::get(Item::IRON_INGOT, 0, mt_rand(3, 5)));
        array_push($drops, Item::get(Item::POPPY, 0, mt_rand(0, 2)));
        return $drops;
    }

    public function getMaxHealth() {
        return 100;
    }

}
