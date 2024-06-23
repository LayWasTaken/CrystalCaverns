<?php

namespace Lay\CrystalCaverns\event;

use Lay\CrystalCaverns\CrystalCaverns;
use Lay\CrystalCaverns\cavern\Cavern;
use Lay\CrystalCaverns\cavern\CavernManager;
use Lay\CrystalCaverns\commands\SpotsCommand;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\world\ChunkLoadEvent;
use pocketmine\event\world\ChunkUnloadEvent;
use pocketmine\event\world\WorldLoadEvent;

final class EventListener implements Listener {

    public function __construct(private CrystalCaverns $plugin) {}

    public function PlayerTeleportEvent(EntityTeleportEvent $e){
        $entity = $e->getEntity();
        if(!($entity instanceof Player)) return;
        $world = $e->getTo()->getWorld();
        if($cavern = CavernManager::getCavern($world->getFolderName())) {
            $helmet = $entity->getArmorInventory()->getHelmet();
            $nbt = $helmet->getNamedTag();
            $tag = $nbt->getTag("ToxicProtection");
            if(!$tag) return $cavern->toxicatePlayer(0, $entity);
            if($cavern->toxicatePlayer($tag->getValue(), $entity)) return;
            Cavern::clearToxicity($entity);
        }
        $worldOrigin = $e->getFrom()->getWorld();
        if(CavernManager::cavernExists($worldOrigin->getFolderName())) Cavern::clearToxicity($entity);
    }

    public function onBlockBreak(BlockBreakEvent $e){
        $block = $e->getBlock();
        $player = $e->getPlayer();
        $world = $player->getWorld();
        if($cavern = CavernManager::getCavern($world->getFolderName())) {
            if($lootBlock = $cavern->getLootOfBlock($block)){
                $drops = $lootBlock->roll();
                $itemParser = StringToItemParser::getInstance();
                foreach ($drops as $drop) {
                    $item = $itemParser->parse($drop[0]);
                    if(!$item) continue;
                    $item->setCount($drop[1]);
                    $player->getInventory()->addItem($item);
                }
            }
        }
    }

    public function onBlockPlace(BlockPlaceEvent $e){
        $player = $e->getPlayer();      
        if(!SpotsCommand::playerExists($player)) return;
        /**@var Block */
        $block = $e->getTransaction()->getBlocks()->current()[3];
        $pos = $block->getPosition();
        if($block->getTypeId() == VanillaBlocks::GOLD()->getTypeId()){
            if(!SpotsCommand::addPosition($player, $pos))  return $player->sendMessage(" Spot already exists");
            $player->sendMessage("Saved Spot at " . $pos->x . " " . $pos->y . " " . $pos->z);
            $pos->getWorld()->setBlockAt($pos->x, $pos->y - 1, $pos->z, VanillaBlocks::EMERALD());
            return $e->cancel();
        }
        if($block->getTypeId() == VanillaBlocks::GLOWSTONE()->getTypeId()) {
            if(!SpotsCommand::removePosition($player, $pos)) {
                $e->cancel();
                $player->sendMessage(" Spot is not saved yet");
                return;
            }
            $e->cancel();
            $player->sendMessage("Spot removed " . $pos->x . " " . $pos->y . " " . $pos->z);
        }
    }

    /**
     * If respawn entity already exists 
     */
}