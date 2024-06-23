<?php

namespace Lay\CrystalCaverns\features;

use Lay\CrystalCaverns\utils\exceptions\LootBlockException;
use Lay\CrystalCaverns\utils\LootTable;
use pocketmine\block\Block;

/**
 * Used to determine the extra drop chance of a block
 */
final class LootBlock {
    private string $blockIDandType;
    private LootTable $lootTable;

    public static function create(Block|string $block, LootTable $lootTable){
        $b = $block;
        if(is_string($block)){
            $seperated = explode(" ", $block);
            if(count($seperated) > 2) throw new LootBlockException("Invalid block");
        }else{
            $b = $block->getTypeId() . " " . $block->getStateId();
        }
        $lootBlock = new self;
        $lootBlock->blockIDandType = $b;
        $lootBlock->lootTable = $lootTable;
        return $lootBlock;
    }

    public function getBlockIDs(){
        return $this->blockIDandType;
    }

    public function isRightBlock(Block $block){
        $b = $block->getTypeId() . " " . $block->getStateId();
        return $b == $this->blockIDandType;
    }

    public function roll(){
        return $this->lootTable->roll();
    }
}