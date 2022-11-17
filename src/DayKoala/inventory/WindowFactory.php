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

namespace DayKoala\inventory;

use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\block\tile\Chest;
use pocketmine\block\tile\NormalFurnace;
use pocketmine\block\tile\Hopper;
use pocketmine\block\BlockLegacyIds;
use DayKoala\inventory\utils\WindowUtils;
use DayKoala\inventory\tile\CustomWindow;
use DayKoala\block\BlockEntityMetadata;
use DayKoala\inventory\tile\FurnaceWindow;
use DayKoala\inventory\tile\DoubleChestWindow;

final class WindowFactory {

    static private ?WindowFactory $instance = null;

    public static function getInstance(): WindowFactory {
        return self::$instance ?? (self::$instance = new WindowFactory());
    }

    private function __construct(
        private array $windows = []
    ) {
        WindowUtils::init();

        $this->register(WindowIds::CHEST, new CustomWindow(WindowTypes::CONTAINER, 27, new BlockEntityMetadata(Chest::class, BlockLegacyIds::CHEST)));
        $this->register(WindowIds::DOUBLE_CHEST, new DoubleChestWindow(WindowTypes::CONTAINER, 54, new BlockEntityMetadata(Chest::class, BlockLegacyIds::CHEST)));
        $this->register(WindowIds::HOPPER, new CustomWindow(WindowTypes::HOPPER, 5, new BlockEntityMetadata(Hopper::class, BlockLegacyIds::HOPPER_BLOCK)));
        $this->register(WindowIds::FURNACE, new FurnaceWindow(WindowTypes::FURNACE, 3, new BlockEntityMetadata(NormalFurnace::class, BlockLegacyIds::FURNACE)));
    }

    public function exists(string $id): bool {
        return isset($this->windows[$id]);
    }

    public function get(string $id, ?string $name = null, bool $clone = true): ?SimpleWindow {
        if (!$this->exists($id)) {
           return null;
        }
        $window = $this->windows[$id];
        
        if ($clone) {
           $window = $window->getClonedInventory();
        }

        if ($name !== null) {
            $window->setName($name);
        }
        return $window;
    }

    public function register(string $id, SimpleWindow $inventory, bool $override = false): void {
        if ($this->exists($id) && !$override) {
            return;
        }
        $this->windows[$id] = $inventory;
    }

    public function unregister(string $id): void {
        if (!$this->exists($id)) {
            return;
        }
        unset($this->windows[$id]);
    }
}