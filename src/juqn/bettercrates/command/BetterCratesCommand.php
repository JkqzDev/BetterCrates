<?php

declare(strict_types=1);

namespace juqn\bettercrates\command;

use juqn\bettercrates\form\CrateManageForm;
use juqn\bettercrates\session\SessionFactory;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class BetterCratesCommand extends Command {

    public function __construct() {
        parent::__construct('crates', 'Command for crates');
        $this->setPermission('crates.command');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender instanceof Player) {
            return;
        }
        
        if (!$this->testPermission($sender)) {
            return;
        }
        $session = SessionFactory::get($sender);

        if ($session === null) {
            return;
        }

        if ($session->getPlaceCrateHandler() !== null) {
            return;
        }
        $form = new CrateManageForm;
        $sender->sendForm($form);
    }
}