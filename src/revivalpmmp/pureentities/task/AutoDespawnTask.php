<?php

namespace revivalpmmp\pureentities\task;

use pocketmine\scheduler\PluginTask;
use revivalpmmp\pureentities\PureEntities;
use revivalpmmp\pureentities\entity\monster\Monster;
use revivalpmmp\pureentities\entity\animal\Animal;

class AutoDespawnTask extends PluginTask {

	private $plugin;

    public function __construct(PureEntities $plugin) {
        parent::__construct($plugin);
        $this->plugin = $plugin;
    }
    
    public function onRun($currentTick){
        PureEntities::logOutput("AutoDespawnTask: onRun ($currentTick)",PureEntities::DEBUG);
        $despawnable = [];
        foreach($this->plugin->getServer()->getLevels() as $level) {
            foreach($level->getEntities() as $entity) {
                $despawnable[$entity->getId()] = 2;
                foreach($level->getPlayers() as $player) {
                    if($player->distance($entity) <= 32) {
                        $despawnable[$entity->getId()] = 1;
                    } elseif($player->distance($entity) >= 128) {
                        $despawnable[$entity->getId()] = 3;
                    }
                }
                
                if($despawnable[$entity->getId()] === 2) {
                    $probability = mt_rand(1, 100);
                    if($probability === 1) {
                        if($entity instanceof Monster) {
                            PureEntities::logOutput("AutoDespawnTask: close monster entity (id: ". $entity->getId() . ", name:" . $entity->getNameTag() .")",PureEntities::DEBUG);
                            $entity->close();
                        }
                    }
                    
                } elseif($despawnable[$entity->getId()] === 3) {
                    if($entity instanceof Animal || $entity instanceof Monster) {
                        PureEntities::logOutput("AutoDespawnTask: close animal/monster entity (id: ". $entity->getId() . ", name:" . $entity->getNameTag() .")",PureEntities::DEBUG);
                        $entity->close();
                    }
                }
            }
        }
    }
}
