<?php

namespace Lay\CrystalCaverns\cavern\caverns;

use Lay\CrystalCaverns\cavern\Cavern;
use Lay\CrystalCaverns\entities\SpotEntities\Skeleton;
use Lay\CrystalCaverns\entities\SpotEntities\Zombie;

final class TitaniumCavern extends Cavern {

    public function getToxication(): int{
        return 2;
    }

    public function getLootBlocks(): array{
        return [
        ];
    }

    public function getSpawningEntities(): array{
        return [
            [Skeleton::class],
            [Zombie::class]
        ];
    }

    public function getWorldFolderName(): string{
        return "TitaniumCavern";
    }

}