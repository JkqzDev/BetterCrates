<?php

declare(strict_types=1);

namespace juqn\bettercrates\crate;

use DayKoala\inventory\action\WindowAction;
use DayKoala\inventory\action\WindowTransaction;
use DayKoala\inventory\tile\CustomWindow;
use DayKoala\inventory\WindowFactory;
use DayKoala\inventory\WindowIds;
use DayKoala\scheduler\WindowWait;
use juqn\bettercrates\block\BlockFactory;
use juqn\bettercrates\util\Utils;
use pocketmine\block\tile\Chest;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\sound\ChestCloseSound;
use pocketmine\world\sound\ChestOpenSound;
use RuntimeException;

final class Crate {

    public function __construct(
        private string $name,
        private string $nameFormat,
        private string $textFormat,
        private Item $keyItem,
        private array $items,
        private array $blocks
    ) {}
    
    public function init(): void {
        foreach ($this->blocks as $position => $data) {
            try {
                $pos = Utils::deserializePosition($position);
                BlockFactory::create($pos, (int) $data['id'], (int) $data['meta'], $this->name);
            } catch (RuntimeException) {}
        }
    }

    public function getName(): string {
        return $this->name;
    }
    
    public function getNameFormat(): string {
        return $this->nameFormat;
    }

    public function getTextFormat(): string {
        return $this->textFormat;
    }
    
    public function getKeyItem(): Item {
        return $this->keyItem;
    }

    public function getItems(): array {
        return $this->items;
    }

    public function giveKey(Player $player, int $count = 1): bool {
        $unbreaking = VanillaEnchantments::UNBREAKING();
        $unbreaking_enchantment = new EnchantmentInstance($unbreaking);
        
        $item = clone $this->keyItem;
        $item->setCount($count);
        $item->setCustomName(TextFormat::colorize('&r' . $this->nameFormat));
        $item->addEnchantment($unbreaking_enchantment);
        $item->getNamedTag()->setString('crate_name', $this->name);
        
        if (!$player->getInventory()->canAddItem($item)) {
            return false;
        }
        $player->getInventory()->addItem($item);
        return true;
    }

    public function giveReward(Player $player): bool {
        $items = $this->items;

        if (count($items) === 0) {
            return false;
        }
        $item = $items[array_rand($items)];

        if (!$player->getInventory()->canAddItem($item)) {
            return false;
        }
        $itemHand = clone $player->getInventory()->getItemInHand();
        $itemHand->pop();
        
        $player->getInventory()->setItemInHand($itemHand);
        $player->getInventory()->addItem($item);
        return true;
    }
    
    public function setNameFormat(string $value): void {
        $this->nameFormat = $value;
    }
    
    public function setTextFormat(string $value): void {
        $this->textFormat = $value;
    }
    
    public function setKeyItem(Item $value): void {
        $this->keyItem = $value;
    }

    public function setItems(array $items): void {
        $this->items = $items;
    }

    public function openCrate(Player $player, Position $pos): void {
        $tile = $pos->getWorld()->getTile($pos);

        if ($tile instanceof Chest) {
            $tile->getInventory()->animateBlock(true);
            $pos->getWorld()->addSound($pos, new ChestOpenSound, [$player]);
        }

        $window = WindowFactory::getInstance()->get(WindowIds::DOUBLE_CHEST, TextFormat::colorize($this->nameFormat));
        assert($window instanceof CustomWindow);
        $window->setContents($this->items);

        $window->setTransaction(function (WindowTransaction $transaction): void {
            $transaction->cancel();
        });
        $window->setCloseCallback(function (WindowAction $action) use ($player, $pos): void {
            $tile = $pos->getWorld()->getTile($pos);

            if ($tile instanceof Chest) {
                $tile->getInventory()->animateBlock(false);
                $pos->getWorld()->addSound($pos, new ChestCloseSound, [$player]);
            }
        });
        WindowWait::addWait($player, $window);
    }

    public function editCrate(Player $player): void {
        $window = WindowFactory::getInstance()->get(WindowIds::DOUBLE_CHEST, TextFormat::colorize($this->nameFormat . ' &r&7(E)'));
        
        if ($window === null) {
            return;
        }
        assert($window instanceof CustomWindow);
        $window->setContents($this->getItems());
        $window->setCloseCallback(function (WindowAction $action): void {
            $player = $action->getPlayer();
            $inventory = $action->getInventory();

            $this?->setItems($inventory->getContents());
            $player->sendMessage(TextFormat::colorize('&aYou have been edited the crate successfully'));
        });
        WindowWait::addWait($player, $window);
    }
    
    public function serializeData(): array {
        $data = [
            'nameFormat' => $this->nameFormat,
            'textFormat' => $this->textFormat,
            'keyItem' => $this->keyItem->getId() . ':' . $this->keyItem->getMeta(),
            'items' => [],
            'blocks' => []
        ];
        
        foreach ($this->items as $slot => $item) {
            $data['items'][$slot] = $item->jsonSerialize();
        }
        
        foreach (BlockFactory::getAll() as $pos => $block) {
            $data['blocks'][$pos] = $block->serializeData();
        }
        return $data;
    }
    
    static public function deserializeData(array $data): array {
        $itemData = explode(':', $data['keyItem']);
        $data['keyItem'] = ItemFactory::getInstance()->get((int) $itemData[0], (int) $itemData[1]);
        
        foreach ($data['items'] as $slot => $item) {
            $data['items'][$slot] = Item::jsonDeserialize($item);
        }
        return $data;
    }
}