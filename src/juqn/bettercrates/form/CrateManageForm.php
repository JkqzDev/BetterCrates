<?php

declare(strict_types=1);

namespace juqn\bettercrates\form;

use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use juqn\bettercrates\crate\CrateFactory;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class CrateManageForm extends SimpleForm {

    public function __construct() {
        parent::__construct(TextFormat::colorize('&cManage Crates'));
        $create = new Button('Create new crate');
        $place = new Button('Place crate');
        $remove = new Button('Remove crate');
        $edit = new Button('Edit crate');

        $this->addButton($create, function (Player $player, int $button_index): void {
            $form = new CrateCreateForm;
            $player->sendForm($form);
        });
        $this->addButton($place, function (Player $player, int $button_index): void {
            if (count(CrateFactory::getAll()) === 0) {
                return;
            }
            $form = new CratePlaceForm;
            $player->sendForm($form);
        });
        $this->addButton($remove, function (Player $player, int $button_index): void {
            if (count(CrateFactory::getAll()) === 0) {
                return;
            }
            $form = new CrateRemoveForm;
            $player->sendForm($form);
        });
        $this->addButton($edit, function (Player $player, int $button_index): void {

        });
    }
}