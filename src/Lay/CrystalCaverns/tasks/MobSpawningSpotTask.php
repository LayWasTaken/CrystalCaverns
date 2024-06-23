<?php

namespace Lay\CrystalCaverns\tasks;

use Lay\CrystalCaverns\cavern\Cavern;
use Lay\CrystalCaverns\cavern\CavernManager;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\scheduler\Task;
use pocketmine\world\Position;

/**@deprecated */
final class MobSpawningSpotTask extends Task{

    /**@var Position[] */
    private static array $positions = [];
    private array $iterations = [];

    public static function addSpot(Position $position){
        $worldName = $position->getWorld()->getFolderName();
        if($cavern = CavernManager::getCavern($worldName)) {
            if(!$cavern->getSpawningEntities()) return;
            if(!array_key_exists($worldName, self::$positions)) self::$positions[$worldName] = [];
            self::$positions[$worldName][] = $position;
        }
    }   

    public function onRun(): void {
        $positions = $this->getNextIteration();
        if(!$positions) return;
        $world = $positions[0]->getWorld();
        $cavern = CavernManager::getCavern($world->getFolderName());
        foreach ($positions as $position) {
            $this->spawnAtSpot($position, $cavern);
        }
    }

    private function spawnAtSpot(Position $position, Cavern $cavern){
        $entitySpawns = $cavern->getSpawningEntities();
        $key = array_rand($entitySpawns);
        /**@var \pocketmine\entity\Entity */
        $entity = new $entitySpawns[$key](Location::fromObject($position, $position->getWorld()), CompoundTag::create());
        $entity->spawnToAll();
    }

    /**@return ?Position[] */
    public function getNextIteration():?array{
        if(!$this->iterations){
            $this->iterations = self::$positions;
            return null;
        }
        return array_pop($this->iterations);
    }
}