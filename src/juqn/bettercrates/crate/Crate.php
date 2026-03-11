<?php

declare(strict_types=1);

namespace juqn\bettercrates\crate;

use JetBrains\PhpStorm\ArrayShape;
use juqn\bettercrates\BetterCrates;
use juqn\bettercrates\block\BlockFactory;
use juqn\bettercrates\util\Utils;
use kim\present\utils\itemserialize\ItemSerializerTrait;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\block\tile\Chest;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\sound\ChestCloseSound;
use pocketmine\world\sound\ChestOpenSound;
use RuntimeException;

final class Crate {
	use ItemSerializerTrait;

    public function __construct(
        private string $name,
        private string $nameFormat,
        private string $textFormat,
        private Item $keyItem,
        private array $items
    ) {}

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

    public function giveKey(Player $player, int $count = 1): bool {
        //$unbreaking = VanillaEnchantments::UNBREAKING();
        //$unbreaking_enchantment = new EnchantmentInstance($unbreaking);
        
        //$item = clone $this->keyItem;
        //$item->setCount($count);
        //$item->setCustomName(TextFormat::colorize('&r' . $this->nameFormat));
        //$item->addEnchantment($unbreaking_enchantment);
        //$item->getNamedTag()->setString('crate_name', $this->name);
		$item = clone $this->keyItem
			->setCount($count)
			->setCustomName(TextFormat::colorize('&r' . $this->nameFormat))
			->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING()))
			->setCustomBlockData(
				CompoundTag::create()
				->setString('crateName', $this->name)
			);

        if (!$player->getInventory()->canAddItem($item)) return false;
        $player->getInventory()->addItem($item);
        return true;
    }

    public function giveReward(Player $player): bool {
        $items = $this->items;

        if (count($items) === 0) return false;
        $item = $items[array_rand($items)];

        if (count($player->getInventory()->getContents()) >= $player->getInventory()->getSize()) return false; // prevent duplicate items
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
        $items = $this->items;
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);

		$colors = DyeColor::getAll();

        for ($i = 0; $i < 27; $i++) {
			if (isset($items[$i])) {
				$menu->getInventory()->setItem($i, $items[$i]);
			} else {
				$menu->getInventory()->setItem($i, VanillaBlocks::STAINED_GLASS_PANE()
					->setColor($colors[array_rand($colors)])
					->asItem()
				);
                //$menu->getInventory()->setItem($i, ItemFactory::getInstance()->get(ItemIds::GLASS_PANE, mt_rand(0, 10)));
            }
		}
        $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
            return $transaction->discard();
        });
        $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($tile, $pos): void {
            if ($tile instanceof Chest) {
                $tile->getInventory()->animateBlock(false);
                $pos->getWorld()->addSound($pos, new ChestCloseSound, [$player]);
            }
        });
        $menu->send($player, TextFormat::colorize($this->nameFormat));
    }

    public function editCrate(Player $player): void {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $menu->getInventory()->setContents($this->items);

        $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory): void {
            $this->setItems($inventory->getContents());
            $player->sendMessage(TextFormat::colorize('&cYou have been edited the create content successfully.'));
        });
        $menu->send($player, TextFormat::colorize($this->nameFormat . ' &r&7(E)'));
    }
    
    #[ArrayShape(['nameFormat' => "string", 'textFormat' => "string", 'keyItem' => "string", 'items' => "array", 'blocks' => "array"])] public function serializeData(): array {
		/*foreach (BlockFactory::getAll() as $pos => $block) {
			if ($block->getCrateName() !== $this->name) continue;
			$data['blocks'][$pos] = $block->serializeData();
		}*/

        return [
            'nameFormat' => $this->nameFormat,
            'textFormat' => $this->textFormat,
			'keyItem' => self::serialize($this->keyItem),
            //'keyItem' => $this->keyItem->getId() . ':' . $this->keyItem->getMeta(),
            'items' => array_map(fn(Item $item) => self::serialize($item), $this->items),
        ];
    }
    
    static public function deserializeData(array $data): array {
        $data['keyItem'] = self::deserialize($data['keyItem']);
		$data['items'] = array_map(fn(string $itemData) => self::deserialize($itemData), $data['items']);
		//$itemData = explode(':', $data['keyItem']);
        //$data['keyItem'] = ItemFactory::getInstance()->get((int) $itemData[0], (int) $itemData[1]);
        
        /*foreach ($data['items'] as $slot => $item) {
            //$data['items'][$slot] = Item::jsonDeserialize($item);
        }*/
        return $data;
    }
}