<?php

declare(strict_types=1);

namespace Lay\CrystalCaverns;

use Lay\CrystalCaverns\entities\SpotEntities\Skeleton;
use Lay\CrystalCaverns\event\EventListener;
use Lay\CrystalCaverns\tasks\HelmetMaskChecker;
use Lay\CrystalCaverns\utils\LootTable;
use Lay\CrystalCaverns\cavern\CavernManager;
use Lay\CrystalCaverns\cavern\caverns\AmethystCavern;
use Lay\CrystalCaverns\cavern\caverns\QuartzCavern;
use Lay\CrystalCaverns\cavern\caverns\TitaniumCavern;
use Lay\CrystalCaverns\commands\SpotsCommand;
use Lay\CrystalCaverns\commands\ToxicityCommand;
use Lay\CrystalCaverns\entities\SpotEntities\Zombie;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\world\Position;
use pocketmine\world\World;

final class CrystalCaverns extends PluginBase{

    private static self $object;

    public static function getInstance(){
        return self::$object;
    }

    public function onLoad(): void {
        $this->registerCommands();
        self::$object = $this;
    }

    public function onEnable(): void {
        $this->initEntities();
        $this->registerLootTables();
        $this->registerCaverns();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getScheduler()->scheduleRepeatingTask(new HelmetMaskChecker, 20 * 2);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if(!$sender instanceof Player) return false;
        switch ($command->getName()) {
            case 'tpworld':
                $world = $this->getServer()->getWorldManager()->getWorldByName($args[0]);
                if($world)
                    $sender->teleport(new Position(0, 100, 0, $world));
                else $sender->sendMessage("World does not exists");
                break;
            default:
                return false;
                break;
        }
        return true;
    }

    private function registerLootTables(){
        LootTable::create("LOW_AMETHYST_DROP", 200, true)
            ->addLoot("diamond", LootTable::LEGENDARY, 0, 2, true)
            ->addLoot("gold_ingot", LootTable::EPIC, 0, 2, true);
        LootTable::create("HIGH_AMETHYST_DROP", 100)
            ->addLoot("diamond", LootTable::RARE, 1, 5, true)
            ->addLoot("gold_ingot", LootTable::COMMON, 1, 4, true);
    }

    private function initEntities(){
        (EntityFactory::getInstance())->register(Skeleton::class, function (World $world, CompoundTag $nbt):Skeleton {
            return new Skeleton(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ["cavern_skeleton"]);
        (EntityFactory::getInstance())->register(Zombie::class, function (World $world, CompoundTag $nbt):Zombie {
            return new Zombie(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ["cavern_zombie"]);
    }

    private function registerCommands(){
        $map = $this->getServer()->getCommandMap();
        $map->register($this->getName(), new SpotsCommand("spots", $this));
        $map->register($this->getName(), new ToxicityCommand("tprotect", $this));
    }

    private function registerCaverns(){
        CavernManager::registerCavern(new AmethystCavern);
        CavernManager::registerCavern(new TitaniumCavern);
        CavernManager::registerCavern(new QuartzCavern);
    }
}
