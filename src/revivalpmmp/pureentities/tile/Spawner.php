<?php

namespace revivalpmmp\pureentities\tile;

use pocketmine\Player;
use pocketmine\tile\Tile;
use revivalpmmp\pureentities\PureEntities;
use pocketmine\level\format\Chunk as FullChunk;
use pocketmine\level\Position;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\tile\Spawnable;
use revivalpmmp\pureentities\task\spawners\BaseSpawner;

class Spawner extends Spawnable {

    protected $entityId = -1;
    protected $spawnRange;
    protected $maxNearbyEntities;
    protected $requiredPlayerRange;

    protected $delay = 0;

    protected $minSpawnDelay;
    protected $maxSpawnDelay;

    public function __construct(FullChunk $chunk, CompoundTag $nbt){
        parent::__construct($chunk, $nbt);

        if(isset($this->namedtag->EntityId)){
            $this->entityId = $this->namedtag["EntityId"];
        }

        if(!isset($this->namedtag->SpawnRange)){
            $this->namedtag->SpawnRange = new ShortTag("SpawnRange", 8);
        }

        if(!isset($this->namedtag->MinSpawnDelay)){
            $this->namedtag->MinSpawnDelay = new ShortTag("MinSpawnDelay", 200);
        }

        if(!isset($this->namedtag->MaxSpawnDelay)){
            $this->namedtag->MaxSpawnDelay = new ShortTag("MaxSpawnDelay", 8000);
        }

        if(!isset($this->namedtag->MaxNearbyEntities)){
            $this->namedtag->MaxNearbyEntities = new ShortTag("MaxNearbyEntities", 25);
        }

        if(!isset($this->namedtag->RequiredPlayerRange)){
            $this->namedtag->RequiredPlayerRange = new ShortTag("RequiredPlayerRange", 20);
        }

        // TODO: add SpawnData: Contains tags to copy to the next spawned entity(s) after spawning. Any of the entity or
        // mob tags may be used. Note that if a spawner specifies any of these tags, almost all variable data such as mob
        // equipment, villager profession, sheep wool color, etc., will not be automatically generated, and must also be
        // manually specified (note that this does not apply to position data, which will be randomized as normal unless
        // Pos is specified. Similarly, unless Size and Health are specified for a Slime or Magma Cube, these will still
        // be randomized). This, together with EntityId, also determines the appearance of the miniature entity spinning
        // in the spawner cage. Note: this tag is optional: if it does not exist, the next spawned entity will use
        // the default vanilla spawning properties for this mob, including potentially randomized armor (this is true even
        // if SpawnPotentials does exist). Warning: If SpawnPotentials exists, this tag will get overwritten after the
        // next spawning attempt: see above for more details.
        if (!isset($this->namedtag->SpawnData)) {
            $this->namedtag->SpawnData = new CompoundTag("SpawnData", [new IntTag("EntityId", $this->entityId)]);
        }

        // TODO: add SpawnCount: How many mobs to attempt to spawn each time. Note: Requires the MinSpawnDelay property to also be set.

        $this->spawnRange = $this->namedtag["SpawnRange"];
        $this->minSpawnDelay = $this->namedtag["MinSpawnDelay"];
        $this->maxSpawnDelay = $this->namedtag["MaxSpawnDelay"];
        $this->maxNearbyEntities = $this->namedtag["MaxNearbyEntities"];
        $this->requiredPlayerRange = $this->namedtag["RequiredPlayerRange"];

        $this->scheduleUpdate();
    }

    public function onUpdate(){
        if($this->closed){
            return false;
        }

        if($this->delay++ >= mt_rand($this->minSpawnDelay, $this->maxSpawnDelay)){
            $this->delay = 0;

            $list = [];
            $isValid = false;
            foreach($this->level->getEntities() as $entity){
                if($entity->distance($this) <= $this->requiredPlayerRange){
                    if($entity instanceof Player){
                        $isValid = true;
                    }
                    $list[] = $entity;
                    break;
                }
            }

            if($isValid && count($list) <= $this->maxNearbyEntities){
                $y = $this->level->getHighestBlockAt($this->x, $this->z);
                $x = $this->x + mt_rand(-$this->spawnRange, $this->spawnRange);
                $z = $this->z + mt_rand(-$this->spawnRange, $this->spawnRange);
                $pos = PureEntities::getFirstAirAbovePosition($x, $y, $z, $this->level); //Why is this starting at the top position?
                $pos->y += BaseSpawner::HEIGHTS[$this->entityId];
                $entity = PureEntities::create($this->entityId, $pos);
                if($entity != null){
                    PureEntities::logOutput("Spawner: spawn $entity to $pos", PureEntities::NORM);
                    $entity->spawnToAll();
                }
            }
        }
        return true;
    }

    public function saveNBT(){
        parent::saveNBT();

        $this->namedtag->EntityId = new ShortTag("EntityId", $this->entityId);
        $this->namedtag->SpawnRange = new ShortTag("SpawnRange", $this->spawnRange);
        $this->namedtag->MinSpawnDelay = new ShortTag("MinSpawnDelay", $this->minSpawnDelay);
        $this->namedtag->MaxSpawnDelay = new ShortTag("MaxSpawnDelay", $this->maxSpawnDelay);
        $this->namedtag->MaxNearbyEntities = new ShortTag("MaxNearbyEntities", $this->maxNearbyEntities);
        $this->namedtag->RequiredPlayerRange = new ShortTag("RequiredPlayerRange", $this->requiredPlayerRange);
        $this->namedtag->SpawnData = new CompoundTag("SpawnData", [new IntTag("EntityId", $this->entityId)]);
    }

    public function getSpawnCompound(){
        return new CompoundTag("", [
            new StringTag("id", Tile::MOB_SPAWNER),
            new IntTag("EntityId", $this->entityId)
        ]);
    }

    public function setSpawnEntityType(int $entityId){
        $this->entityId = $entityId;
        $this->namedtag->EntityId = new ShortTag("EntityId", $this->entityId);
        $this->namedtag->SpawnData = new CompoundTag("SpawnData", [
            new IntTag("EntityId", $this->entityId)
        ]);
        $this->spawnToAll();
    }

    public function setMinSpawnDelay(int $minDelay){
        if($minDelay > $this->maxSpawnDelay){
            return;
        }

        $this->minSpawnDelay = $minDelay;
    }

    public function setMaxSpawnDelay(int $maxDelay){
        if($this->minSpawnDelay > $maxDelay){
            return;
        }

        $this->maxSpawnDelay = $maxDelay;
    }

    public function setSpawnDelay(int $minDelay, int $maxDelay){
        if($minDelay > $maxDelay){
            return;
        }

        $this->minSpawnDelay = $minDelay;
        $this->maxSpawnDelay = $maxDelay;
    }

    public function setRequiredPlayerRange(int $range){
        $this->requiredPlayerRange = $range;
    }

    public function setMaxNearbyEntities(int $count){
        $this->maxNearbyEntities = $count;
    }

}
