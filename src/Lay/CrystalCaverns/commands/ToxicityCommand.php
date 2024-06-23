<?php

namespace Lay\CrystalCaverns\commands;

use Lay\CrystalCaverns\CrystalCaverns;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

class ToxicityCommand extends Command implements PluginOwned{

    private CrystalCaverns $owningPlugin;

    public function __construct(string $name, CrystalCaverns $plugin){
		$this->setPermission("perm");
		$this->owningPlugin = $plugin;
		$this->usageMessage = "";
		parent::__construct($name);
	}


    public function getOwningPlugin(): Plugin{
        return $this->owningPlugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender instanceof Player) return;
        if(!array_key_exists(0, $args)){
            $sender->sendMessage("Missing #0 argument - must include the level of the protection");
            return;
        }
        $item = VanillaItems::DIAMOND_HELMET();
        $nbt = $item->getNamedTag() ?? new CompoundTag("", []);
        $nbt->setTag("ToxicProtection", new IntTag((int)$args[0]));
        $item->setNamedTag($nbt);
        $sender->getInventory()->addItem($item);
    }

}