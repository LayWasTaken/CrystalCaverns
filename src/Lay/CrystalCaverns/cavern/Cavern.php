<?php

namespace Lay\CrystalCaverns\cavern;

use Lay\CrystalCaverns\CrystalCaverns;
use Lay\CrystalCaverns\entities\RespawnEntity;
use pocketmine\block\Block;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\Player;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;
use Lay\CrystalCaverns\features\LootBlock;
use Lay\CrystalCaverns\utils\exceptions\CavernException;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\utils\Config;
use pocketmine\world\Position;

abstract class Cavern {

    private ?World $world;

    /**@var LootBlock[] */
    private array $lootBlock = [];

    /**@var bool[] */
    private array $spots = [];

    public function __construct(){
        $worldManager = Server::getInstance()->getWorldManager();
        $this->world = $worldManager->getWorldByName($this->getWorldFolderName());
        if(!$this->world) {
            $worldManager->generateWorld($this->getWorldFolderName(), WorldCreationOptions::create()->setDifficulty(1));
            $worldManager->loadWorld($this->getWorldFolderName());
            $this->world = $worldManager->getWorldByName($this->getWorldFolderName());
        }
        foreach ($this->getLootBlocks() as $lootBlock) {
            if(!$lootBlock instanceof LootBlock) throw new CavernException("Invalid array of loot blocks");
            $this->lootBlock[$lootBlock->getBlockIDs()] = $lootBlock;
        }
    }

    public static function clearToxicity(Player $player){
        $player->getEffects()->remove(VanillaEffects::NAUSEA());
        $player->getEffects()->remove(VanillaEffects::POISON());
    }

    public function getWorld(){
        return $this->world;
    }

    public function toxicatePlayer(int $level, Player $player): bool{
        if($level >= $this->getToxication()) return false;
        $player->getEffects()->add(new EffectInstance(VanillaEffects::POISON(), 999999, 2));
        $player->getEffects()->add(new EffectInstance(VanillaEffects::NAUSEA(), 999999, 2));
        return true;
    }

    public function getLootOfBlock(Block $block){
        $b = $block->getTypeId() . " " . $block->getStateId();
        return array_key_exists($b, $this->lootBlock) ? $this->lootBlock[$b] : null;
    }

    // public function spawnRandomEntityAtPosition(Vector3 $vector3, float $yaw = 0, float $pitch = 0, ?CompoundTag $overrideTag = null){
    //     $this->spawnRandomEntityAtLocation(Location::fromObject($vector3, $this->world, $yaw, $pitch), $overrideTag);
    // }

    public function spawnSpotEntityAtLocation(Location $location, Vector3 $boundSpot, ?CompoundTag $overrideTag = null){
        if($this->isSpotOwned($boundSpot)) return;
        $entitySetting = $this->getSpawningEntities()[array_rand($this->getSpawningEntities())];
        $entityClass = $entitySetting[0];
        $entityTag = $overrideTag ?? (array_key_exists(1, $entitySetting) ? clone $entitySetting[1] : CompoundTag::create());
        /**@var Entity */
        $entity = new $entityClass($location, $entityTag, $boundSpot);
        $entity->spawnToAll();
        $this->flagOwnedSpot($boundSpot, true);
    }

    public function respawnAtAllSpots(){
        $config = new Config(CrystalCaverns::getInstance()->getDataFolder() . "/spots/" . $this->getSpotsJSONFile() . ".json", Config::JSON, []);
        $spots = $config->getAll();
        if(!$spots) return;
        $spawnedEntities = [];
        foreach($spots as $xyz){
            $this->world->loadChunk($xyz[0] >> 4, $xyz[2] >> 4);
            $stringspot = $xyz[0] . $xyz[1] . $xyz[2];
            $this->spots[$stringspot] = false;
            $spot = (new Vector3($xyz[0], $xyz[1], $xyz[2]));
            $this->spawnSpotEntityAtLocation(Location::fromObject($spot->add(0.5, 0.8, 0.5), $this->getWorld(), mt_rand(0, 35) * 100), $spot);
        }
        foreach($spawnedEntities as $extra){
            $extra->flagForDespawn();
            $extra->kill();
        }
    }

    public function flagOwnedSpot(Vector3 $vector3, bool $owned):bool {
        $stringSpot = $vector3->x . $vector3->y . $vector3->z;
        if(!array_key_exists($stringSpot, $this->spots)) return false;
        if($this->spots[$stringSpot] && $owned) return false;
        $this->spots[$stringSpot] = $owned;
        return true;
    }

    public function isSpotOwned(Vector3 $vector3){
        $stringSpot = $vector3->x . $vector3->y . $vector3->z;
        if(!array_key_exists($stringSpot, $this->spots)) return false;
        return $this->spots[$stringSpot];
    }

    public abstract function getToxication(): int;

    /**@return array{0: string, 1: CompoundTag}[] */
    public abstract function getSpawningEntities(): array;

    /**@return string Must return the world name and this also determines the name of the cavern */
    public abstract function getWorldFolderName(): string;

    /**@return LootBlock[] */
    public abstract function getLootBlocks(): array;

    /**@return string [optional] Return the file name of the lists of coordinates of the spots */
    public function getSpotsJSONFile(): ?string { return null; }
}