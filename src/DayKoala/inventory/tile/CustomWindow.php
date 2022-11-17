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

namespace DayKoala\inventory\tile;

use Closure;
use pocketmine\item\Item;
use pocketmine\player\Player;
use DayKoala\inventory\SimpleWindow;
use DayKoala\inventory\utils\WindowCallbacksTrait;
use DayKoala\inventory\action\WindowAction;

class CustomWindow extends SimpleWindow {
    use WindowCallbacksTrait;

    public function setItem(int $index, Item $item, ?Closure $callback = null): void {
        $target = $this->getItem($index);

        if (!$target->equalsExact($item)) {
            if($this->hasItemCallback($target)) {
                $this->removeItemCallback($target);
            }
        }
        parent::setItem($index, $item);

        if ($callback) {
            $this->setItemCallback($item, $callback);
        }
    }

    public function onContentChange(array $items): void {
        foreach($items as $index => $item) {
            if (!$this->hasItemCallback($item)) {
                continue;
            }
            $target = $this->getItem($index);

            if (!$target->equalsExact($item)) {
                $this->removeItemCallback($item);
            }
        }
    }

    public function onClose(Player $who): void {
        parent::onClose($who);

        if ($this->hasCloseCallback()) {
            $callback = $this->getCloseCallback();
            $callback(new WindowAction($this, $who));
        }
    }

    public function getClonedInventory(): static {
        $window = parent::getClonedInventory();
        $window->copyCallbacks($this->transaction, $this->closeCallback, $this->itemCallback);
        return $window;
    }

}