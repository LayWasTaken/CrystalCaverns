<?php

namespace Lay\CrystalCaverns\entities;

use Lay\CrystalCaverns\cavern\CavernManager;
use Lay\CrystalCaverns\CrystalCaverns;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\Position;

abstract class RespawnEntity extends Living{

    const SPAWN_ORIGIN = "spawnspot";

    const DEFAULT_DESPAWN_DISTANCE = 10;
    const TICKS_FOR_DESPAWN = 120;

    const TICKS_FOR_CHECKING_NEARBY_ENEMY = 10;

    protected ?Vector3 $spotLocation = null;
    private int $despawnTickCounter = 0;
    private int $tickCounter = 1;

    public function __construct(Location $location, ?CompoundTag $nbt = null, ?Vector3 $spawnSpot = null){
        parent::__construct($location, $nbt);
        if(!$spawnSpot) 
            $this->flagForDespawn();
        else 
             $this->spotLocation = $spawnSpot->floor();
    }

    /**@return Position */
    public function getSpawnSpotPosition(){
        return $this->spotLocation;
    }

    // public function saveNBT(): CompoundTag{
    //     $nbt = parent::saveNBT();
    //     $nbt->setTag(RespawnEntity::SPAWN_ORIGIN, new ListTag([
    //         new DoubleTag($this->spotLocation->x),
    //         new DoubleTag($this->spotLocation->y),
    //         new DoubleTag($this->spotLocation->z)
    //     ]));
    //     return $nbt;
    // }

    // protected function initEntity(CompoundTag $nbt): void {
    //     $spawnOrigin = $nbt->getListTag(self::SPAWN_ORIGIN)->getAllValues();
    //     $this->spotLocation = $this->spotLocation ?? new Location($spawnOrigin[0], $spawnOrigin[1], $spawnOrigin[2], $this->getWorld(), 0, 0);
    //     parent::initEntity($nbt);
    // }

    protected function onDeath(): void {
        parent::onDeath();
        $spot = $this->spotLocation;
        $location = Location::fromObject($this->spotLocation->add(0.5, 1, 0.5), $this->location->getWorld(), mt_rand(0, 35) * 100);
        if(!$cavern = CavernManager::getCavern($this->getWorld()->getFolderName())) return;
        if(!$cavern->flagOwnedSpot($this->spotLocation, false)) return;
        CrystalCaverns::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($cavern, $location, $spot){
            if(!$cavern->isSpotOwned($spot)) return $cavern->spawnSpotEntityAtLocation($location, $spot);
        }), 20 * 8);
    }

    protected function entityBaseTick(int $tickDiff = 1): bool{
        parent::entityBaseTick($tickDiff);
        if($this->tickCounter == self::TICKS_FOR_CHECKING_NEARBY_ENEMY){
            $pos = $this->getPosition();
            $entities = $this->getWorld()->getNearbyEntities(new AxisAlignedBB(
                $pos->x - 5,
                $pos->y - 1,
                $pos->z - 5,
                $pos->x + 5,
                $pos->y + 2,
                $pos->z + 5
            ));
            $nearbyTarget = false;
            foreach($entities as $entity){
                if($entity instanceof Player) {
                    $this->setTargetEntity($entity);
                    $this->tickCounter++;
                    $nearbyTarget = true;
                }
            }
            if(!$nearbyTarget) $this->setTargetEntity(null);
        }
        if($this->tickCounter >= 20) $this->tickCounter = 1;
        $this->tickCounter++;
        if($targetEntity = $this->getTargetEntity()) {
            $this->lookAt($targetEntity->getPosition()->add(0, 1, 0));
            $this->despawnTickCounter = 0;
            return true;
        }
        if($this->despawnTickCounter < self::TICKS_FOR_DESPAWN) {
            $this->despawnTickCounter++;
            return true;
        }
        $this->despawnTickCounter = 0;
        if($this->isNearSpot()) return true;
        $this->teleport($this->getSpawnSpotPosition()->add(0.5, 0, 0.5));
        $this->heal(new EntityRegainHealthEvent($this, $this->getMaxHealth(), EntityRegainHealthEvent::CAUSE_CUSTOM));
        return true;
    }

    private function isNearSpot(){
        return $this->getPosition()->distance($this->spotLocation) <= self::DEFAULT_DESPAWN_DISTANCE;
    }

    // private function followPlayer(Player $player){
    //     $playerPos = $player->getPosition()->floor();
    //     $entityPos = $this->getPosition();
    //     $floorX = $entityPos->getFloorX();
    //     $x = $floorX == $playerPos->getFloorX() ? $floorX : ($floorX < $);
    // }
}