<?php

namespace Lay\CrystalCaverns\cavern\caverns;

use Lay\CrystalCaverns\cavern\Cavern;
use Lay\CrystalCaverns\entities\SpotEntities\Skeleton;
use Lay\CrystalCaverns\entities\SpotEntities\Zombie;
use Lay\CrystalCaverns\features\LootBlock;
use Lay\CrystalCaverns\utils\LootTable;
use pocketmine\block\VanillaBlocks;

final class AmethystCavern extends Cavern {

    public function getToxication(): int{
        return 1;
    }

    public function getLootBlocks(): array{
        return [
            LootBlock::create(VanillaBlocks::AMETHYST(), LootTable::getLootTable("LOW_AMETHYST_DROP")),
            LootBlock::create(VanillaBlocks::BUDDING_AMETHYST(), LootTable::getLootTable("HIGH_AMETHYST_DROP"))
        ];
    }

    public function getSpawningEntities(): array{
        return [
            [Skeleton::class],
            [Zombie::class]
        ];
    }

    public function getWorldFolderName(): string{
        return "AmethystCavern";
    }

    public function getSpotsJSONFile(): ?string{
        return "AmethystCavern";
    }
}