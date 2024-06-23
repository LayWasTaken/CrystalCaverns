<?php

namespace Lay\CrystalCaverns\cavern\caverns;

use Lay\CrystalCaverns\cavern\Cavern;
use Lay\CrystalCaverns\entities\SpotEntities\Skeleton;
use Lay\CrystalCaverns\entities\SpotEntities\Zombie;

final class QuartzCavern extends Cavern {

    public function getToxication(): int{
        return 3;
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
        return "QuartzCavern";
    }

}