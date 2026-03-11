<?php

declare(strict_types=1);

namespace juqn\bettercrates\form;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Dropdown;
use juqn\bettercrates\block\BlockFactory;
use juqn\bettercrates\crate\CrateFactory;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class CrateRemoveForm {

	public static function create(Player $player) : void {
		$names = array_keys(CrateFactory::getAll());

		$form = new CustomForm(
			TextFormat::colorize('&rRemove a Crate'),
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

				foreach (BlockFactory::getAll() as $blocks) {
					foreach ($blocks as $block) {
						$crate = $block->getCrate();

						if ($crate === null || $crate->getName() !== $crateName) continue;
						$position = $block->getPosition();
						BlockFactory::remove($position);
					}
				}
				CrateFactory::remove($crateName);

				$player->sendMessage(TextFormat::colorize('&cCrate has been removed'));
			}
		);

		$player->sendForm($form);
	}

    /*public function __construct() {
        parent::__construct(TextFormat::colorize('&cRemove Crate'));
        $crates = array_keys(CrateFactory::getAll());
        $cratesDropdown = new DropdownEntry('Choose a crate', $crates);

        $this->addEntry($cratesDropdown, function (Player $player, DropdownEntry $entry, int $value) use ($crates): void {
            $crateName = $crates[$value];

            if (CrateFactory::get($crateName) === null) {
                $player->sendMessage(TextFormat::colorize('&cCrate not exists.'));
                return;
            }
            foreach (BlockFactory::getAll() as $pos => $block) {
                if ($block->getCrateName() === $crateName) {
                    $block->getText()?->flagForDespawn();
                    BlockFactory::remove($pos);
                }
            }
            CrateFactory::remove($crateName);
            $player->sendMessage(TextFormat::colorize('&cCrate has been removed'));
        });
    }*/
}