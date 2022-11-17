<?php

declare(strict_types=1);

namespace juqn\bettercrates\form;

use cosmicpe\form\CustomForm;
use cosmicpe\form\entries\custom\DropdownEntry;
use cosmicpe\form\entries\custom\InputEntry;
use juqn\bettercrates\block\Block;
use juqn\bettercrates\block\BlockFactory;
use juqn\bettercrates\crate\CrateFactory;
use pocketmine\item\ItemFactory;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class CrateEditForm extends CustomForm {

    public function __construct() {
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
                private $crateName,
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
                            assert($block instanceof Block);
                            $block->getText()?->setNameTag(TextFormat::colorize($this->textFormat));
                        }
                    }

                    $player->sendMessage(TextFormat::colorize('&aYou have been edit the crate successfully'));
                });
            }
        };
    }
}