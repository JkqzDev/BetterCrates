<?php

declare(strict_types=1);

namespace juqn\bettercrates\block;

use juqn\bettercrates\block\Block as CrateBlock;
use juqn\bettercrates\util\Utils;
use pocketmine\world\Position;

final class BlockFactory {

    /** @var Block[] */
    static private array $blocks = [];
    
    static public function getAll(): array {
        return self::$blocks;
    }

    static public function get(Position|string $position): ?CrateBlock {
        $pos = $position instanceof Position ? Utils::positionToString($position) : $position;
        return self::$blocks[$pos] ?? null;
    }

    static public function create(Position $position, int $id, int $meta, string $createName): void {
        self::$blocks[Utils::positionToString($position)] = new CrateBlock($position, $createName, $id, $meta);
    }

    static public function remove(Position|string $position): void {
        $pos = $position instanceof Position ? Utils::positionToString($position) : $position;

        if (self::get($pos) === null) {
            return;
        }
        unset(self::$blocks[$pos]);
    }
}