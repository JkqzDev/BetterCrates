<?php

declare(strict_types=1);

namespace juqn\bettercrates\block;

use juqn\bettercrates\crate\Crate;
use juqn\bettercrates\crate\CrateFactory;
use juqn\bettercrates\entity\TextEntity;
use juqn\bettercrates\util\Utils;
use kim\present\utils\itemserialize\ItemSerializerTrait;
use pocketmine\entity\Location;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

final class Block {
	use ItemSerializerTrait;

	private TextEntity $text;

    public function __construct(
        private Position $position,
        private \pocketmine\block\Block $block,
		private string $crateName,
    ) {}

	public function getPosition() : Position {
		return $this->position;
	}

    public function getCrate(): ?Crate {
        return CrateFactory::get($this->crateName);
    }

    public function getText(): TextEntity {
        return $this->text;
    }

	public function spawn() : void {
		$crate = $this->getCrate();
		$position = $this->position;

		if ($crate === null) return;
		$this->text = new TextEntity(Location::fromObject($position->add(0.5, 1.3, 0.5), $position->getWorld()));
		$this->text->setNameTag(TextFormat::colorize($crate->getTextFormat()));
		$this->text->spawnToAll();
	}

	public function despawn() : void {
		if (!isset($this->text)) return;

		if (!$this->text->isClosed()) $this->text->flagForDespawn();
		unset($this->text);
	}

	public static function deserializeData(array $data) : Block {
		$position = Utils::stringToPosition($data['position']);
		$block = self::deserialize($data['block'])->getBlock();

		return new Block(
			$position,
			$block,
			$data['crate']
		);
	}
    
    public function serializeData(): array {
        return [
			'position' => Utils::positionToString($this->position),
			'crate' => $this->crateName,
			'block' => self::serialize($this->block->asItem()) // hack.
        ];
    }
}