<?php

declare(strict_types=1);

namespace juqn\bettercrates\form;


use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class CrateManageForm {

	public static function create(Player $player) : void {
		$form = new MenuForm(
			TextFormat::colorize('&rManage Crates'),
			TextFormat::colorize('&7Select a option'),
			[
				new MenuOption('Create a new crate'),
				new MenuOption('Place a crate'),
				new MenuOption('Remove a crate'),
				new MenuOption('Edit a crate'),
			],
			function (Player $player, int $selectedOption) : void {
				match ($selectedOption) {
					0 => CrateCreateForm::create($player),
					1 => CratePlaceForm::create($player),
					2 => CrateRemoveForm::create($player),
					3 => CrateEditForm::create($player),
					default => null,
				};
			}
		);
	}

    /*public function __construct() {
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
            if (count(CrateFactory::getAll()) === 0) {
                return;
            }
            $form = new CrateEditForm;
            $player->sendForm($form);
        });
    }*/
}