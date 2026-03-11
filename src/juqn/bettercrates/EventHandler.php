<?php

declare(strict_types=1);

namespace juqn\bettercrates;

use juqn\bettercrates\block\BlockFactory;
use juqn\bettercrates\session\SessionFactory;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\world\ChunkLoadEvent;
use pocketmine\event\world\ChunkUnloadEvent;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\utils\TextFormat;

final class EventHandler implements Listener {

	/**
	 * @param BlockBreakEvent $event
	 * @priority HIGHEST
	 * @return void
	 */
    public function handleBreak(BlockBreakEvent $event): void {
        $block = $event->getBlock();
        $crateBlock = BlockFactory::get($block->getPosition());

        if ($crateBlock !== null) $event->cancel();
    }

	/**
	 * @param BlockPlaceEvent $event
	 * @priority HIGHEST
	 * @return void
	 */
    public function handlePlace(BlockPlaceEvent $event): void {
		$blockTransaction = $event->getTransaction();
		$player = $event->getPlayer();

		$session = SessionFactory::get($player);

		if ($session === null) return;

		/** @var Block $block */
		foreach ($blockTransaction->getBlocks() as [, , , $block]) {
			$handler = $session->getPlaceCrateHandler();

			if ($handler === null) break;
			$handler->place($block);
			$session->stopPlaceCrateHandler();
		}
        /*$block = $event->getBlock();
        $player = $event->getPlayer();
        
        $session = SessionFactory::get($player);

        if ($session === null) return;
        $handler = $session->getPlaceCrateHandler();

        if ($handler === null) return;
        $handler->place($block);
        $session->stopPlaceCrateHandler();

        $player->sendMessage(TextFormat::colorize('&aYou have placed the crate successfully'));*/
    }

    public function handleInteract(PlayerInteractEvent $event): void {
        $action = $event->getAction();
        $block = $event->getBlock();
        $item = $event->getItem();
        $player = $event->getPlayer();
        $crateBlock = BlockFactory::get($block->getPosition());

        if ($crateBlock === null) return;
		$event->cancel();
        $crate = $crateBlock->getCrate();

        if ($crate === null) return;

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

			if (!$item->hasCustomBlockData() || $item->getCustomBlockData()->getTag('crateName') === null) return;
			$crateName = $item->getCustomBlockData()->getString('crateName');
            //if ($item->getNamedTag()->getTag('crate_name') === null) return;
            //$crateName = $item->getNamedTag()->getString('crate_name');

            if ($crateName !== $crate->getName()) return;
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

	public function handleLoad(ChunkLoadEvent $event) : void {
		[$chunkX, $chunkZ] = [$event->getChunkX(), $event->getChunkZ()];
		$world = $event->getWorld();

		$blocks = BlockFactory::getAllFromChunk($world, $chunkX, $chunkZ);

		foreach ($blocks as $block) {
			$block->spawn();
		}
	}

	public function handleUnload(ChunkUnloadEvent $event) : void {
		[$chunkX, $chunkZ] = [$event->getChunkX(), $event->getChunkZ()];
		$world = $event->getWorld();

		$blocks = BlockFactory::getAllFromChunk($world, $chunkX, $chunkZ);

		foreach ($blocks as $block) {
			$block->despawn();
		}
	}
}