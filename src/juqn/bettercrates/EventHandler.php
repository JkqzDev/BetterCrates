<?php

declare(strict_types=1);

namespace juqn\bettercrates;

use juqn\bettercrates\block\BlockFactory;
use juqn\bettercrates\session\SessionFactory;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\utils\TextFormat;

final class EventHandler implements Listener {

    public function handleBreak(BlockBreakEvent $event): void {
        $block = $event->getBlock();
        $crateBlock = BlockFactory::get($block->getPosition());

        if ($crateBlock !== null) {
            $event->cancel();
        }
    }

    public function handlePlace(BlockPlaceEvent $event): void {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        
        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }
        $handler = $session->getPlaceCrateHandler();

        if ($handler === null) {
            return;
        }
        $handler->place($block);
        $session->stopPlaceCrateHandler();

        $player->sendMessage(TextFormat::colorize('&aYou have placed the crate successfully'));
    }

    public function handleInteract(PlayerInteractEvent $event): void {
        $action = $event->getAction();
        $block = $event->getBlock();
        $item = $event->getItem();
        $player = $event->getPlayer();
        $crateBlock = BlockFactory::get($block->getPosition());

        if ($crateBlock === null) {
            return;
        }
        $crate = $crateBlock->getCrate();

        if ($crate === null) {
            return;
        }
        $event->cancel();

        if ($action === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
            if ($player->getServer()->isOp($player->getName()) && $player->getGamemode()->equals(GameMode::CREATIVE())) {
                $crate->editCrate($player);
                return;
            }
            $crate->openCrate($player, $block->getPosition());
        } else {
            if ($player->getServer()->isOp($player->getName()) && $player->getGamemode()->equals(GameMode::CREATIVE()) && $player->getInventory()->getItemInHand()->equals(VanillaItems::STICK())) {
                $crateBlock->getText()?->flagForDespawn();
                BlockFactory::remove($block->getPosition());
                $player->sendMessage(TextFormat::colorize('&cCrate has been removed'));
                return;
            }
            if ($item->getNamedTag()->getTag('crate_name') === null) {
                return;
            }
            $crateName = $item->getNamedTag()->getString('crate_name');

            if ($crateName !== $crate->getName()) {
                return;
            }
            $crate->giveReward($player);
        }
    }

    public function handleJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        SessionFactory::create($player);
    }

    public function handleQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        SessionFactory::remove($player);
    }
}