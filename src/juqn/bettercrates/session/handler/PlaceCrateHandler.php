<?php

declare(strict_types=1);

namespace juqn\bettercrates\session\handler;

use juqn\bettercrates\block\BlockFactory;
use juqn\bettercrates\crate\CrateFactory;
use pocketmine\block\Block;

final class PlaceCrateHandler {

    public function __construct(
        private string $crateName
    ) {}

    public function place(Block $block): void {
        //$crate = CrateFactory::get($this->crateName);

		BlockFactory::create($block->getPosition(), $block, $this->crateName);
        //BlockFactory::create($block->getPosition(), $block->getId(), $block->getMeta(), $crate->getName());
    }
}