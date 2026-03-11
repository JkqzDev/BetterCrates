<?php

declare(strict_types=1);

namespace juqn\bettercrates\form;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Dropdown;
use dktapps\pmforms\element\Input;
use juqn\bettercrates\block\BlockFactory;
use juqn\bettercrates\crate\CrateFactory;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class CrateEditForm {

	public static function create(Player $player) : void {
		$names = array_keys(CrateFactory::getAll());

		$form = new CustomForm(
			TextFormat::colorize('&rEdit Crate'),
			[
				new Dropdown(
					'crate',
					'Choose an crate',
					$names
				)
			],
			function (Player $player, CustomFormResponse $response) use ($names) : void {
				$key = $response->getInt('crate');
				$crateName = $names[$key];

				if (CrateFactory::get($crateName) === null) {
					$player->sendMessage(TextFormat::colorize('&cCrate not exists.'));
					return;
				}
				self::crateEdit($player, $crateName);
			}
		);

		$player->sendForm($form);
	}

	private static function crateEdit(Player $player, string $crateName) : void {
		$crate = CrateFactory::get($crateName);

		if ($crate === null) return;
		$form = new CustomForm(
			TextFormat::colorize('&rEdit Crate'),
			[
				new Input('customName', 'Custom Item Name', 'Example: &6Legend', $crate->getNameFormat()),
				new Input('textFormat', 'Custom Floating Text', 'Example: &6Legend\n&7Right click to open!', $crate->getTextFormat()),
				new Input('itemKey', 'Item Key', 'Example: 322:0', StringToItemParser::getInstance()->lookupAliases($crate->getKeyItem()) ?? $crate->getKeyItem()->getVanillaName())
			],
			function (Player $player, CustomFormResponse $response) use ($crate) : void {
				$customName = $response->getString('customName');
				$textFormat = $response->getString('textFormat');
				$itemKey = $response->getString('itemKey');

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
				$oldTextFormat = $crate->getTextFormat();

				if ($oldTextFormat !== $textFormat) {
					foreach (BlockFactory::getAll() as $blocks) {
						foreach ($blocks as $block) {
							$c = $block->getCrate();

							if ($c === null || $c->getName() !== $crate->getName()) continue;
							$block->despawn(); // hack
							$block->spawn(); // hack
						}
					}
				}
				$crate->setNameFormat($customName);
				$crate->setTextFormat($textFormat);
				$crate->setKeyItem($item);

				$player->sendMessage(TextFormat::colorize('&aYou have been edit the crate successfully'));
			}
		);

		$player->sendForm($form);
	}
    /*public function __construct() {
        parent::__construct(TextFormat::colorize('&cEdit Crate'));
        $crates = array_keys(CrateFactory::getAll());
        $cratesDropdown = new DropdownEntry('Choose a crate', $crates);

        $this->addEntry($cratesDropdown, function (Player $player, DropdownEntry $entry, int $value) use ($crates): void {
            $crateName = $crates[$value];

            if (CrateFactory::get($crateName) === null) {
                $player->sendMessage(TextFormat::colorize('&cCrate not exists.'));
                return;
            }
            $form = $this->createEditForm($crateName);
            $player->sendForm($form);
        });
    }

    private function createEditForm(string $crateName): CustomForm {
        return new class($crateName) extends CustomForm {

            public function __construct(
                string $crateName,
                private ?string $nameFormat = null,
                private ?string $textFormat = null
            ) {
                parent::__construct(TextFormat::colorize('&cEdit ' . $crateName . ' crate'));
                $crate = CrateFactory::get($crateName);

                $nameFormat = new InputEntry('Name Format', 'Title for item name', $crate->getNameFormat());
                $textFormat = new InputEntry('Text Format', 'Text for floating text', $crate->getTextFormat());
                $item = new InputEntry('Key Item', 'Item for crate key', $crate->getKeyItem()->getId() . ':' . $crate->getKeyItem()->getMeta());

                $this->addEntry($nameFormat, function (Player $player, InputEntry $entry, string $value): void {
                    $this->nameFormat = str_replace('\n', '', $value);
                });
                $this->addEntry($textFormat, function (Player $player, InputEntry $entry, string $value): void {
                    $this->textFormat = str_replace('\n', PHP_EOL, $value);
                });
                $this->addEntry($item, function (Player $player, InputEntry $entry, string $value) use ($crateName): void {
                    $crate = CrateFactory::get($crateName);

                    if ($crate === null) {
                        $player->sendMessage(TextFormat::colorize('&cCrate not exists!'));
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
                    $oldTextFormat = $crate->getTextFormat();

                    $item = ItemFactory::getInstance()->get((int) $v[0], isset($v[1]) ? (int) $v[1] : 0);
                    $crate->setKeyItem($item);
                    $crate->setTextFormat($this->textFormat);
                    $crate->setNameFormat($this->nameFormat);

                    if ($oldTextFormat !== $this->textFormat) {
                        foreach (BlockFactory::getAll() as $block) {
                            $block->getText()?->setNameTag(TextFormat::colorize($this->textFormat));
                        }
                    }
                    $player->sendMessage(TextFormat::colorize('&aYou have been edit the crate successfully'));
                });
            }
        };
    }*/
}