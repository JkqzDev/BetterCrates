<?php

declare(strict_types=1);

namespace juqn\bettercrates\form;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use juqn\bettercrates\crate\CrateFactory;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\inventory\Inventory;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class CrateCreateForm {

	public static function create(Player $player) : void {
		$form = new CustomForm(
			TextFormat::colorize('&rCreate a new crate'),
			[
				new Input('name', 'Name', 'Example: legend'),
				new Input('customName', 'Custom Item Name', 'Example: &6Legend'),
				new Input('textFormat', 'Custom Floating Text', 'Example: &6Legend\n&7Right click to open!'),
				new Input('itemKey', 'Item Key', 'Example: 322:0')
			],
			function (Player $player, CustomFormResponse $response) : void {
				$name = $response->getString('name');
				$customName = $response->getString('customName');
				$textFormat = $response->getString('textFormat');
				$itemKey = $response->getString('itemKey');

				if (CrateFactory::get($name) !== null) {
					$player->sendMessage(TextFormat::colorize('&cCrate already exists.'));
					return;
				}

				if (trim($customName) === '') {
					$player->sendMessage(TextFormat::colorize('&cInvalid custom item name'));
					return;
				}

				if (trim($textFormat) === '') {
					$player->sendMessage(TextFormat::colorize('&cInvalid custom floating text'));
					return;
				}
				$item = StringToItemParser::getInstance()->parse($itemKey) ?? LegacyStringToItemParser::getInstance()->parse($itemKey) ?? null;

				if ($item === null) {
					$player->sendMessage(TextFormat::colorize('&cInvalid key item.'));
					return;
				}
				CrateFactory::create($name, $customName, $textFormat, $item, []);

				self::createMenuForItems($player, $name);
				//CrateFactory::create($this->name, $this->nameFormat, $this->textFormat, $item);
			}
		);

		$player->sendForm($form);
	}

	private static function createMenuForItems(Player $player, string $crateName): void {
		$crate = CrateFactory::get($crateName);

		if ($crate === null) return;
		$menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
		$menu->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($crate): void {
			$crate->setItems($inventory->getContents());
			$player->sendMessage(TextFormat::colorize('&aYou have been create the crate successfully.'));
		});
		$menu->send($player, TextFormat::colorize('&cCreate Content'));
	}

    /*public function __construct(
        private ?string $name = null,
        private ?string $nameFormat = null,
        private ?string $textFormat = null
    ) {
        parent::__construct(TextFormat::colorize('Create Crate'));
        $name = new InputEntry('Name', 'Crate Name');
        $nameFormat = new InputEntry('Name Format', 'Title for item name');
        $textFormat = new InputEntry('Text Format', 'Text for floating text');
        $item = new InputEntry('Key Item', 'Item for crate key', '131:0');

        $this->addEntry($name, function (Player $player, InputEntry $entry, string $value): void {
            if (CrateFactory::get($value) !== null) {
                return;
            }
            $this->name = $value;
        });
        $this->addEntry($nameFormat, function (Player $player, InputEntry $entry, string $value): void {
            $this->nameFormat = str_replace('\n', '', $value);
        });
        $this->addEntry($textFormat, function (Player $player, InputEntry $entry, string $value): void {
            $this->textFormat = str_replace('\n', PHP_EOL, $value);
        });
        $this->addEntry($item, function (Player $player, InputEntry $entry, string $value): void {
            if ($this->name === null) {
                $player->sendMessage(TextFormat::colorize('&cCrate already exists'));
                return;
            }

            if ($this->nameFormat === null) {
                return;
            }

            if ($this->textFormat === null) {
                return;
            }
            $v = explode(':', $value);

            if (!is_numeric($v[0])) {
                $player->sendMessage(TextFormat::colorize('&cUse numbers for key item'));
                return;
            }

            if (isset($v[1]) && !is_numeric($v[1])) {
                $player->sendMessage(TextFormat::colorize('&cUse numbers for key item'));
                return;
            }
            $item = ItemFactory::getInstance()->get((int) $v[0], isset($v[1]) ? (int) $v[1] : 0);
            CrateFactory::create($this->name, $this->nameFormat, $this->textFormat, $item);

            $this->createMenuForItems($player, $this->name);
        });
    }

    private function createMenuForItems(Player $player, string $crateName): void {
        $crate = CrateFactory::get($crateName);

        if ($crate === null) {
            return;
        }
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($crate): void {
            $crate->setItems($inventory->getContents());
            $player->sendMessage(TextFormat::colorize('&aYou have been create the crate successfully.'));
        });
        $menu->send($player, TextFormat::colorize('&cCreate Content'));
    }*/
}