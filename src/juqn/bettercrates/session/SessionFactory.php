<?php

declare(strict_types=1);

namespace juqn\bettercrates\session;

use pocketmine\player\Player;

final class SessionFactory {

    static private array $sessions = [];

    static public function get(Player $player): ?Session {
        return self::$sessions[$player->getXuid()] ?? null;
    }

    static public function create(Player $player): void {
        self::$sessions[$player->getXuid()] = new Session;
    }

    static public function remove(Player $player): void {
        if (self::get($player) === null) return;
        unset(self::$sessions[$player->getXuid()]);
    }
}