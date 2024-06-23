<?php

namespace Lay\CrystalCaverns\entities\SpotEntities;

use Lay\CrystalCaverns\entities\RespawnEntity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

final class Skeleton extends RespawnEntity {

    public static function getNetworkTypeId() : string{ return EntityIds::SKELETON; }
 
    protected function getInitialSizeInfo() : EntitySizeInfo{
        return new EntitySizeInfo(1.8, 0.6); //TODO: eye height ??
    }
 
    public function getName() : string{
        return "Skeleton";
    }
 
    public function getDrops() : array{
        $drops = [
            VanillaItems::BONE()->setCount(mt_rand(0, 2))
        ];

        switch(mt_rand(0, 1)){
            case 0:
                $drops[] = VanillaItems::ARROW();
                break;
            case 1:
                $drops[] = VanillaItems::BOW();
                break;
        }
        return $drops;
    }
 
    public function getXpDropAmount() : int{
        return 5;
    }

}