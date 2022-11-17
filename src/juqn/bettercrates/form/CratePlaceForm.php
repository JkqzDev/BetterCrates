<?php

declare(strict_types=1);

namespace juqn\bettercrates\form;

use cosmicpe\form\CustomForm;
use cosmicpe\form\entries\custom\DropdownEntry;
use juqn\bettercrates\crate\CrateFactory;
use juqn\bettercrates\session\SessionFactory;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class CratePlaceForm extends CustomForm {

    public function __construct() {
        parent::__construct(TextFormat::colorize('&cPlace Crate'));
        $crates = array_keys(CrateFactory::getAll());
        $cratesDropdown = new DropdownEntry('Choose a crate', $crates);

        $this->addEntry($cratesDropdown, function (Player $player, DropdownEntry $entry, int $value) use ($crates): void {
            $crateName = $crates[$value];

            if (CrateFactory::get($crateName) === null) {
                $player->sendMessage(TextFormat::colorize('&cCrate not exists.'));
                return;
            }
            $session = SessionFactory::get($player);

            if ($session === null) {
                return;
            }
            $session->startPlaceCrateHandler($crateName);
            $player->sendMessage(TextFormat::colorize('&aChoose the block and place'));
        });
    }
}