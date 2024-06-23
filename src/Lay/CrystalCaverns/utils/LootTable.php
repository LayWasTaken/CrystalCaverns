<?php

namespace Lay\CrystalCaverns\utils;

use Lay\CrystalCaverns\utils\exceptions\LootTableExistsException;

final class LootTable {

    /**
     * @var self[]
     */
    private static $LootTables = [];
    private array $LootPool;
    private bool $oneDrop = false;
    private int $maxRange = 100;
    
    const COMMON = 1;
    const RARE = 0.6;
    const EPIC = 0.4;
    const LEGENDARY = 0.1;

    /**
     * @param bool $oneDrop If true then it will only drop one of the given drops
     * @param int $maxRange If the lowest chance is 0.01 then it must be 100 if it is 0.0001 then it must be 10000
     */
    public static function create(string|int $id, int $maxRange = 100, bool $oneDrop = false){
        if(array_key_exists($id, self::$LootTables)) throw new LootTableExistsException("Table ID Already Exists");
        $table = self::$LootTables[$id] = new self;
        $table->oneDrop = $oneDrop;
        $table->maxRange = $maxRange;
        return $table;
    }

    public static function getLootTable(string|int $id){
        if(!array_key_exists($id, self::$LootTables)) return null;
        return clone self::$LootTables[$id];
    }

    /**
     * @param string $drop The id of the drop
     * @param float $weight The chance of getting the item. Must be less than or equal to the set max range.
     * @param int $minAmount The guranteed minimum amount if the drop is chosen
     * @param int $maxAmount [optional] It will roll again and if it is chosen then it will increase the amount base on the first chance. 
     * @param bool $exponentialRarityAmount Will exponentially increase the rarity for each amount of drops
     */
    public function addLoot(
        string $drop,
        float $weight,
        int $minAmount,
        int $maxAmount = 0,
        bool $exponentialRarityAmount = false
    ){
        $this->LootPool[] = [
            $drop, 
            ($weight * 100) > (1 * $this->maxRange) ? (1 * $this->maxRange) : ($weight * 100), 
            $minAmount < 0 ? 0 : $minAmount,
            $maxAmount < $minAmount ? $minAmount : $maxAmount,
            $exponentialRarityAmount
        ];
        if($this->oneDrop) 
            usort($this->LootPool, fn($a, $b) => $b[1] <=> $a[1]);
        return $this;
    }
    
    /**
     * @return array Key 0 is the drop, Key 1 is the Amount
     */
    public function roll(){
        if($this->oneDrop) return $this->rollUniqueDrop();
        return $this->rollDrop();
    }

    private function rollDrop(){
        $drops = [];
        foreach ($this->LootPool as $loot) {
            $roll = mt_rand(1, $this->maxRange);
            if($roll > $loot[1]) continue;
            $amount = $loot[2];
            if($loot[2] != $loot[3]){
                if($loot[4]) $amount += $this->exponentialDrops($loot[1], $loot[3], $loot[2]);
                else $amount += mt_rand(0, $loot[3] - $loot[2]);
            }
            $drops[] = [$loot[0], $amount];
        }
        return $drops;
    }

    private function rollUniqueDrop(){
        $drop = [];
        foreach ($this->LootPool as $loot) {
            $roll = mt_rand(1, $this->maxRange);
            if($roll > $loot[1]) continue;
            $amount = $loot[2];
            if($loot[2] != $loot[3]){
                if($loot[4]) $amount += $this->exponentialDrops($loot[1], $loot[3], $loot[2]);
                else $amount += mt_rand(0, $loot[3] - $loot[2]);
            }
            $drop[] = [$loot[0], $amount];
            break;
        }
        return $drop;
    }

    private function exponentialDrops(int $weight, int $max, int $current = 1): int{
        $iteration = $current;
        while($iteration < $max){
            if((mt_rand(1, $this->maxRange) > $weight)) return $iteration;
            $iteration++;
            if($iteration >= $max - $current) return $iteration;
        }
        return $iteration;
    }
}