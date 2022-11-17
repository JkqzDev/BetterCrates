<?php

declare(strict_types=1);

namespace juqn\bettercrates\form;

use cosmicpe\form\CustomForm;
use cosmicpe\form\entries\custom\DropdownEntry;
use juqn\bettercrates\crate\CrateFactory;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class CrateRemoveForm extends CustomForm {

    public function __construct() {
        parent::__construct(TextFormat::colorize('&cRemove Crate'));
        $crates = array_keys(CrateFactory::getAll());
        $cratesDropdown = new DropdownEntry('Crates', $crates);

        $this->addEntry($cratesDropdown, function (Player $player, DropdownEntry $entry, int $value) use ($crates): void {
            $crateName = $crates[$value];

            if (CrateFactory::get($crateName) === null) {
                $player->sendMessage(TextFormat::colorize('&cCrate not exists.'));
                return;
            }
            CrateFactory::remove($crateName);
            $player->sendMessage(TextFormat::colorize('&cCrate has been removed'));
        });
    }
}