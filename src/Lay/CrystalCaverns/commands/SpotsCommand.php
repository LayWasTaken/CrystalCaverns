<?php

namespace Lay\CrystalCaverns\commands;

use Lay\CrystalCaverns\CrystalCaverns;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

class SpotsCommand extends Command implements PluginOwned{
    
    private $owningPlugin;

	/**@var array{0: int, 1: int, 2: int}[] */
	private static array $active = [];

	/**@return Return true if success and false if not */
	public static function addPosition(Player $player, Position $position){
		if(!self::playerExists($player)) return false;
		$posAsArray = [$position->x, $position->y, $position->z];
		$xuid = $player->getXuid();
		if(in_array($posAsArray, self::$active[$xuid])) return false;
		self::$active[$xuid][] = $posAsArray;
		return true;
	}

	/**@return Return true if success and false if not */
	public static function removePosition(Player $player, Position $position){
		if(!self::playerExists($player)) return false;
		$posAsArray = [$position->x, $position->y, $position->z];
		$xuid = $player->getXuid();
		if($key = array_search($posAsArray, self::$active[$xuid])) {
			unset(self::$active[$xuid][$key]);
			return true;
		}
		return false;
	}

	public static function playerExists(Player $player){
		return array_key_exists($player->getXuid(), self::$active);
	}

	public function __construct(string $name, CrystalCaverns $plugin){
		$this->setPermission("perm");
		$this->owningPlugin = $plugin;
		$this->usageMessage = "";
		@mkdir($this->owningPlugin->getDataFolder() . "/spots/");
		parent::__construct($name);
	}

	public function getOwningPlugin() : CrystalCaverns{
		return $this->owningPlugin;
	}

    public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$sender instanceof Player){
			$sender->sendMessage("Not Player");
			return false;
		}
        $xuid = $sender->getXuid();
		switch (array_key_exists(0, $args) ? $args[0] : 1) {
			case 'start':
				if(array_key_exists($xuid, self::$active)){
					$sender->sendMessage("Already within queue");
					return false;
				}
				self::$active[$xuid] = [];
				$sender->sendMessage("Added to queue");
				$sender->sendMessage(TextFormat::RESET . TextFormat::GREEN . "Place the spot with " . TextFormat::GOLD . "GOLD BLOCK" . TextFormat::GREEN . " to save the spot and place a " . TextFormat::GOLD . "GLOWSTONE BLOCK" . TextFormat::GREEN . "  to remove the spot from the list");
				break;
			
			case 'save':
				if(!array_key_exists($xuid, self::$active)) {
					$sender->sendMessage("Not within queue");
					return false;
				}
				if(!array_key_exists(1, $args)) {
					$sender->sendMessage("Invalid #2 argument - Add file name");
					return false;
				}
				$path = $this->owningPlugin->getDataFolder() . "/spots/" . $args[1] . ".json";
				$config = new Config($path, Config::JSON, self::$active[$xuid]);
				$config->setAll(self::$active[$xuid]);
				$config->save();
				unset(self::$active[$xuid]);
				$sender->sendMessage("Successfully saved at " . $path);
				break;

			case 'cancel':
				if(!array_key_exists($xuid, self::$active)) {
					$sender->sendMessage("Not within queue");
					return false;
				}
				unset(self::$active[$xuid]);
				$sender->sendMessage("Successfully removed from the queue");
				break;
			default:
				$sender->sendMessage("Invalid #0 argument - must include 'save' to save the current spots or 'start' to start creating spawn spots or 'cancel' to cancel the current created spots");
				break;
		}
    }
}