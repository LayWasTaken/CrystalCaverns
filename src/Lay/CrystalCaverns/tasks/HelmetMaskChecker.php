<?php

namespace Lay\CrystalCaverns\tasks;

use Lay\CrystalCaverns\cavern\Cavern;
use Lay\CrystalCaverns\cavern\CavernManager;
use pocketmine\scheduler\Task;
use pocketmine\Server;

final class HelmetMaskChecker extends Task {
    
    private static $currentIteration = 0;

    public function onRun(): void {
        $cavernNames = CavernManager::getCavernsNames();
        if(!$cavernNames) return;
        $worldName = $cavernNames[self::$currentIteration];
        $world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);
        $cavern = CavernManager::getCavern($worldName);
        if($world->isLoaded()){
            foreach ($world->getPlayers() as $player) {
                $helmet = $player->getArmorInventory()->getHelmet();
                $nbt = $helmet->getNamedTag();
                $tag = $nbt->getTag("ToxicProtection");
                if($tag == null){
                    $cavern->toxicatePlayer(0, $player);
                    continue;
                }
                if($cavern->toxicatePlayer($tag->getValue(), $player)) continue;
                Cavern::clearToxicity($player);
            }
        }
        if((count($cavernNames) - 1) == self::$currentIteration) self::$currentIteration = 0;
        else ++self::$currentIteration;
    }
}