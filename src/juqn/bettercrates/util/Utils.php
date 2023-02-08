<?php

declare(strict_types=1);

namespace juqn\bettercrates\util;

use pocketmine\Server;
use pocketmine\world\Position;
use RuntimeException;

final class Utils {

    static public function positionToString(Position $position): string {
        [$world, $x, $y, $z] = [$position->getWorld()->getFolderName(), $position->getFloorX(), $position->getFloorY(), $position->getFloorZ()];
        return $world . ':' . $x . ':' . $y . ':' . $z;
    }

    static public function stringToPosition(string $data): Position {
        $data = explode(':', $data);

        if (!Server::getInstance()->getInstance()->getWorldManager()->isWorldGenerated($data[0])) {
            throw new RuntimeException('World isn\'t generated');
        }

        if (!Server::getInstance()->getInstance()->getWorldManager()->isWorldLoaded($data[0])) {
            Server::getInstance()->getWorldManager()->loadWorld($data[0]);
        }
        return new Position((float) $data[1], (float) $data[2], (float) $data[3], Server::getInstance()->getWorldManager()->getWorldByName($data[0]));
    }
}