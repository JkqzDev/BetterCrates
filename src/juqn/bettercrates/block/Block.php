<?php

declare(strict_types=1);

namespace juqn\bettercrates\block;

use JetBrains\PhpStorm\ArrayShape;
use juqn\bettercrates\crate\Crate;
use juqn\bettercrates\crate\CrateFactory;
use juqn\bettercrates\entity\TextEntity;
use pocketmine\entity\Location;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

final class Block {

    public function __construct(
        Position $position,
        private string $crateName,
        private int $id,
        private int $meta,
        private ?TextEntity $text = null
    ) {
        $crate = $this->getCrate();

        if ($crate !== null) {
            $this->text = new TextEntity(Location::fromObject($position->add(0.5, 1.3, 0.5), $position->getWorld()), null, $this->crateName);
            $this->text->setNameTag(TextFormat::colorize($crate->getTextFormat()));
            $this->text->setCanSaveWithChunk(true);
            $this->text->spawnToAll();
        }
    }

    public function getCrateName(): string {
        return $this->crateName;
    }

    public function getCrate(): ?Crate {
        return CrateFactory::get($this->crateName);
    }

    public function getText(): ?TextEntity {
        return $this->text;
    }

    public function setText(?TextEntity $text): void {
        $this->text = $text;
    }
    
    #[ArrayShape(['id' => "int", 'meta' => "int"])] public function serializeData(): array {
        return [
            'id' => $this->id,
            'meta' => $this->meta
        ];
    }
}