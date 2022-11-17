<?php

declare(strict_types=1);

namespace juqn\bettercrates\form;

use cosmicpe\form\CustomForm;
use cosmicpe\form\entries\custom\InputEntry;
use DayKoala\inventory\action\WindowAction;
use DayKoala\inventory\tile\CustomWindow;
use DayKoala\inventory\WindowFactory;
use DayKoala\inventory\WindowIds;
use DayKoala\scheduler\WindowWait;
use juqn\bettercrates\crate\CrateFactory;
use pocketmine\item\ItemFactory;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class CrateCreateForm extends CustomForm {

    public function __construct(
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
        $window = WindowFactory::getInstance()->get(WindowIds::DOUBLE_CHEST, TextFormat::colorize('&cCrate Edit'));
        
        if ($window === null) {
            return;
        }
        assert($window instanceof CustomWindow);
        $window->setCloseCallback(function (WindowAction $action) use ($crate): void {
            $player = $action->getPlayer();
            $inventory = $action->getInventory();

            $crate?->setItems($inventory->getContents());
            $player->sendMessage(TextFormat::colorize('&aYou have been create the crate successfully'));
        });
        WindowWait::addWait($player, $window);
    }
}