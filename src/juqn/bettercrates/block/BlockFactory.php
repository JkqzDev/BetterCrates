<?php

declare(strict_types=1);

namespace juqn\bettercrates\block;

use juqn\bettercrates\BetterCrates;
use juqn\bettercrates\util\Utils;
use pocketmine\utils\Config;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\World;

final class BlockFactory {

	/** @var array<string, array<string, Block>> */
	static private array $blocks = [];

	static public function get(Position $position) : ?Block {
		$hash = World::chunkHash($position->getFloorX() >> Chunk::COORD_BIT_SIZE, $position->getFloorZ() >> Chunk::COORD_BIT_SIZE);
		$worldName = $position->getWorld()->getFolderName();

		$key = $worldName . '-' . $hash;
		$pos = Utils::positionToString($position);

		return self::$blocks[$key][$pos] ?? null;
	}

	static public function getAllFromChunk(World $world, int $chunkX, int $chunkZ) : array {
		$hash = World::chunkHash($chunkX, $chunkZ);
		$worldName = $world->getFolderName();

		//$hash = World::chunkHash($position->getFloorX() >> Chunk::COORD_BIT_SIZE, $position->getFloorZ() >> Chunk::COORD_BIT_SIZE);
		//$worldName = $position->getWorld()->getFolderName();

		$key = $worldName . '-' . $hash;

		return self::$blocks[$key] ?? [];
	}

	static public function getAll() : array {
		return self::$blocks;
	}

	static public function create(Position $position, \pocketmine\block\Block $block, string $crateName) : void {
		$hash = World::chunkHash($position->getFloorX() >> Chunk::COORD_BIT_SIZE, $position->getFloorZ() >> Chunk::COORD_BIT_SIZE);
		$worldName = $position->getWorld()->getFolderName();

		$key = $worldName . '-' . $hash;
		$pos = Utils::positionToString($position);

		self::$blocks[$key][$pos] = new Block(
			$position,
			$block,
			$crateName
		);
	}

	static public function remove(Position $position) : void {
		if (self::get($position) === null) return;
		$hash = World::chunkHash($position->getFloorX() >> Chunk::COORD_BIT_SIZE, $position->getFloorZ() >> Chunk::COORD_BIT_SIZE);
		$worldName = $position->getWorld()->getFolderName();

		$key = $worldName . '-' . $hash;
		$pos = Utils::positionToString($position);

		unset(self::$blocks[$key][$pos]);
	}

	static public function loadAll() : void {
		$config = new Config(
			BetterCrates::getInstance()->getDataFolder() . 'blocks.json',
			Config::JSON
		);

		$data = $config->getAll();

		foreach ($data as $id => $b) {
			foreach ($b as $position => $d) {
				$block = Block::deserializeData($d);

				if ($block->getCrate() === null) continue;
				self::$blocks[$id][$position] = $block;
			}
		}
	}

	static public function saveAll() : void {
		$config = new Config(
			BetterCrates::getInstance()->getDataFolder() . 'blocks.json',
			Config::JSON
		);

		$data = [];

		foreach (self::$blocks as $id => $b) {
			foreach ($b as $position => $block) {
				$data[$id][$position] = $block->serializeData(); // need change this.
			}
		}

		$config->setAll($data);
		$config->save();
	}

    /**
    static private array $blocks = [];
    
    static public function getAll(): array {
        return self::$blocks;
    }

    static public function get(Position|string $position): ?CrateBlock {
        $pos = $position instanceof Position ? Utils::positionToString($position) : $position;
        return self::$blocks[$pos] ?? null;
    }

    static public function create(Position $position, int $id, int $meta, string $createName): void {
        self::$blocks[Utils::positionToString($position)] = new CrateBlock($position, $createName, $id, $meta);
    }

    static public function remove(Position|string $position): void {
        $pos = $position instanceof Position ? Utils::positionToString($position) : $position;

        if (self::get($pos) === null) {
            return;
        }
        unset(self::$blocks[$pos]);
    }*/
}