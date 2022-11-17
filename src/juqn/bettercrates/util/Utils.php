<?php

declare(strict_types=1);

namespace juqn\bettercrates\util;

use pocketmine\block\Block;
use pocketmine\Server;
use pocketmine\world\Position;
use RuntimeException;

final class Utils {

    static public function serializePosition(Position $position): string {
        [$world, $x, $y, $z] = [$position->getWorld()->getFolderName(), $position->getFloorX(), $position->getFloorY(), $position->getFloorZ()];
        return $world . ':' . $x . ':' . $y . ':' . $z;
    }

    static public function deserializePosition(string $data): Position {
        $data = explode(':', $data);

        if (!Server::getInstance()->getInstance()->getWorldManager()->isWorldGenerated($data[0])) {
            throw new RuntimeException('World isnt generated');
        }

        if (!Server::getInstance()->getInstance()->getWorldManager()->isWorldLoaded($data[0])) {
            Server::getInstance()->getWorldManager()->loadWorld($data[0]);
        }
        return new Position((int) $data[1], (int) $data[2], (int) $data[3], Server::getInstance()->getWorldManager()->getWorldByName($data[0]));
    }
}