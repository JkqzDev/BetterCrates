<?php

/*
 *  __          ___           _                     
 *  \ \        / (_)         | |                    
 *   \ \  /\  / / _ _ __   __| | _____      ___   _ 
 *    \ \/  \/ / | | '_ \ / _` |/ _ \ \ /\ / / | | |
 *     \  /\  /  | | | | | (_| | (_) \ V  V /| |_| |
 *      \/  \/   |_|_| |_|\__,_|\___/ \_/\_/  \__, |
 *                                             __/ |
 *                                            |___/ 
 *  @author DayKoala
 *  @link https://github.com/DayKoala/Windowy
 *  @social https://twitter.com/DayKoala
 * 
 */

namespace DayKoala;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskScheduler;
use DayKoala\inventory\SimpleWindow;
use DayKoala\inventory\WindowFactory;
use RuntimeException;

final class Windowy {

    static private bool $registered = false;
    static private ?TaskScheduler $scheduler = null;

    static public function isRegistered(): bool {
        return self::$registered;
    }

    static public function getTaskScheduler(): ?TaskScheduler {
        return self::$scheduler;
    }

    static public function register(PluginBase $plugin): void {
        if (self::isRegistered()) {
            throw new RuntimeException('Windowy has been already registered');
        }
        self::$registered = true;
        self::$scheduler = $plugin->getScheduler();

        $plugin->getServer()->getPluginManager()->registerEvents(new WindowListener, $plugin);
    }
}