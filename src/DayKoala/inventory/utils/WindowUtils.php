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

namespace DayKoala\inventory\utils;

use Closure;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use DayKoala\inventory\SimpleWindow;
use DayKoala\inventory\WindowFactory;
use DayKoala\inventory\WindowIds;
use RuntimeException;

final class WindowUtils {

    static private ?Closure $callback = null;

    static public function init() {
        self::$callback = function(int $id, SimpleWindow $inventory) {
            return [ContainerOpenPacket::blockInv($id, $inventory->getNetworkType(), BlockPosition::fromVector3($inventory->getPosition()))];
        };
    }

    static public function hasCallback(Player $player): bool {
        $inventoryManager = $player->getNetworkSession()->getInvManager();
        return $inventoryManager ? $inventoryManager->getContainerOpenCallbacks()->contains(self::$callback) : false;
    }

    static public function setCallback(Closure $callback): void {
        if (self::isCallbackValid($callback)) {
           self::$callback = $callback;
        } else {
           throw new RuntimeException("Invalid Window Callback", 1);
        }
    }

    static public function addCallback(Player $player): void {
        $inventoryManager = $player->getNetworkSession()->getInvManager();

        if ($inventoryManager !== null) {
           if ($inventoryManager->getContainerOpenCallbacks()->contains(self::$callback)) {
              return;
           }
           $inventoryManager->getContainerOpenCallbacks()->add(self::$callback);
        }
    }

    static public function removeCallback(Player $player): void {
        $inventoryManager = $player->getNetworkSession()->getInvManager();

        if ($inventoryManager !== null) {
           if (!$inventoryManager->getContainerOpenCallbacks()->contains(self::$callback)) {
              return;
           }
           $inventoryManager->getContainerOpenCallbacks()->remove(self::$callback);
        }
    }

    static public function isCallbackValid(Closure $callback): bool {
        return is_array($callback(WindowTypes::CONTAINER, WindowFactory::getInstance()->get(WindowIds::CHEST)));
    }
}