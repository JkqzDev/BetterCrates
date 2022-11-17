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

namespace DayKoala\scheduler;

use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use DayKoala\inventory\SimpleWindow;
use DayKoala\Windowy;

final class WindowWait extends Task {

    static protected array $wait = [];

    static public function inWait(Player|string $player): bool {
        if ($player instanceof Player) {
           $player = $player->getXuid();
        }
        return isset(self::$wait[$player]);
    }

    static public function getWait(Player|string $player): ?WindowWait {
        if ($player instanceof Player) {
           $player = $player->getXuid();
        }
        return self::$wait[$player] ?? null;
    }

    static public function addWait(Player $player, SimpleWindow $inventory): bool {
        if (isset(self::$wait[$player->getXuid()])) {
           return false;
        }
        $current = $player->getCurrentWindow();

        if ($current instanceof SimpleWindow) {
           $current->onRemove($player);
        }
        $time = 4;

        Windowy::getTaskScheduler()->scheduleRepeatingTask(self::$wait[$player->getXuid()] = new self($player, $inventory, $time), 2);
        return true;
    }

    static public function cancelWait(Player|int $player): void {
        if ($player instanceof Player) {
           $player = $player->getXuid();
        }

        if (isset(self::$wait[$player])) {
           $task = self::$wait[$player];
           
           if (!$task->getHandler()->isCancelled()){
              $task->getHandler()->cancel();
           } else {
              unset(self::$wait[$player]);
           }
        }
    }

    protected int $min, $max;

    protected function __construct(
        protected Player $player,
        protected SimpleWindow $inventory,
        int $time,
        protected bool $created = false
    ) {
        $this->min = $time;
        $this->max = $time;
    }

    public function getInventory(): SimpleWindow {
        return $this->inventory;
    }

    public function getPlayer(): Player {
        return $this->player;
    }

    public function getTime(): int {
        return $this->min;
    }

    public function getMaxTime(): int {
        return $this->max;
    }

    public function isCreated(): bool {
        return $this->created;
    }

    public function onRun(): void {
        if ($this->min == floor($this->max / 2)) {
           $this->created = $this->inventory->onCreate($this->player);
        } elseif($this->min == 1 && $this->created) {
           $this->player->setCurrentWindow($this->inventory);
        } elseif($this->min == 0) {
           $this->getHandler()->cancel();
           return;
        }
        $this->min--;
    }

    public function onCancel(): void {
        if (isset(self::$wait[$this->player->getXuid()])) {
            unset(self::$wait[$this->player->getXuid()]);
        }
    }
}