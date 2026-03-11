<?php

declare(strict_types=1);

namespace juqn\bettercrates\command;

use juqn\bettercrates\crate\CrateFactory;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class BetterKeyCommand extends Command {

    public function __construct() {
        parent::__construct('key', 'Command for give key');
        $this->setPermission('key.command');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$this->testPermission($sender)) return;

        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::colorize('&cUse /key help'));
            return;
        }

        switch(strtolower($args[0])) {
            case 'help':
                $messages = [
                    '&r&cCrate Command Help',
                    '&r&f/key give - &cUse this command to give keys',
                    '&r&f/key giveall - &cUse this command to give all key.',
                ];
                $sender->sendMessage(TextFormat::colorize(implode(PHP_EOL, $messages)));
                break;

            case 'give':
                if (count($args) < 4) {
                    $sender->sendMessage(TextFormat::colorize('&cUse /key give [nick] [crate] [amount]'));
                    return;
                }
                $player = $sender->getServer()->getPlayerByPrefix($args[1]);
                $crateName = $args[2];
                $amount = $args[3];
                
                if (!$player instanceof Player) {
                    $sender->sendMessage(TextFormat::colorize('&cPlayer offline'));
                    return;
                }
                $crate = CrateFactory::get($crateName);

                if ($crate === null) {
                    $sender->sendMessage(TextFormat::colorize('&cCrate not found'));
                    return;
                }

                if (!is_numeric($amount)) {
                    $sender->sendMessage(TextFormat::colorize('&cUse numbers for the amout'));
                    return;
                }
                $crate->giveKey($player, (int) $amount);

                $sender->sendMessage(TextFormat::colorize('&aYou have given the player ' . $amount . 'x ' . $crateName . ' crate'));
                $player->sendMessage(TextFormat::colorize('&aYou have received ' . $amount . 'x ' . $crateName . ' crate'));
                break;

                case 'giveall':
                    if (count($args) < 3) {
                        $sender->sendMessage(TextFormat::colorize('&cUse /key giveall [crate] [amount]'));
                        return;
                    }
                    $crateName = $args[1];
                    $amount = $args[2];
                    
                    $crate = CrateFactory::get($crateName);
                    
                    if ($crate === null) {
                        $sender->sendMessage(TextFormat::colorize('&cCrate not found'));
                        return;
                    }
                    
                    if (!is_numeric($amount)) {
                        $sender->sendMessage(TextFormat::colorize('&cUse numbers for the amout'));
                        return;
                    }
                    $sender->sendMessage(TextFormat::colorize('&aYou have given for the all players ' . $amount . 'x ' . $crateName . ' crate'));
                    
                    foreach ($sender->getServer()->getOnlinePlayers() as $player) {
                        $crate->giveKey($player, (int) $amount);
                        $player->sendMessage(TextFormat::colorize('&aYou have received ' . $amount . 'x ' . $crateName . ' crate'));
                    }
                    break;

                default:
                    $sender->sendMessage(TextFormat::colorize('&cSubcommand not found. Use /key help'));
                    break;
        }
    }
}