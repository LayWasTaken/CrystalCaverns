<?php

namespace Lay\CrystalCaverns\cavern;

use Lay\CrystalCaverns\utils\exceptions\CavernException;

final class CavernManager {

    /**@var Cavern[] */
    private static $caverns = [];

    public static function registerCavern(Cavern $cavern){
        $cavernName = $cavern->getWorld()->getFolderName();
        if(self::cavernExists($cavernName)) throw new CavernException("Cavern already exists");
        $cloned = self::$caverns[$cavernName] = clone $cavern;
        $cloned->respawnAtAllSpots();
    }

    public static function getCavern(string $worldName):?Cavern{
        return self::cavernExists($worldName) ? self::$caverns[$worldName] : null;
    }

    public static function cavernExists(string $worldName){
        return array_key_exists($worldName, self::$caverns);
    }

    public static function getCaverns(){
        return self::$caverns;
    }

    public static function getCavernsNames(){
        return array_keys(self::$caverns);
    }
    
}