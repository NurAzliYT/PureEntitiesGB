<?php

namespace revivalpmmp\pureentities\entity\animal\walking;

use revivalpmmp\pureentities\entity\animal\WalkingAnimal;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\entity\Creature;

class Ocelot extends WalkingAnimal{
    const NETWORK_ID = 22;

    public $width = 0.72;
    public $height = 0.9;

    public function getSpeed() : float{
        return 1.4;
    }

    public function getName(){
        return "Ocelot";
    }

    public function targetOption(Creature $creature, float $distance) : bool{
        if($creature instanceof Player){
            return $creature->spawned && $creature->isAlive() && !$creature->closed && $creature->getInventory()->getItemInHand()->getId() == Item::RAW_FISH && $distance <= 49;
        }
        return false;
    }

    public function getDrops(){
        return [];
    }

    public function getMaxHealth() {
        return 10;
    }
}
