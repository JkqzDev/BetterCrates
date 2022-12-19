<?php

declare(strict_types=1);

namespace juqn\bettercrates\crate;

use juqn\bettercrates\BetterCrates;
use pocketmine\item\Item;
use pocketmine\utils\Config;

final class CrateFactory {

    /** @var Crate[] */
    static private array $crates = [];
    
    static public function getAll(): array {
        return self::$crates;
    }

    static public function get(string $name): ?Crate {
        return self::$crates[$name] ?? null;
    }

    static public function create(string $name, string $nameFormat, string $textFormat, Item $keyItem, array $items = [], array $blocks = []): void {
        self::$crates[$name] = $crate = new Crate($name, $nameFormat, $textFormat, $keyItem, $items, $blocks);
        $crate->init();
    }

    static public function remove(string $name): void {
        if (self::get($name) === null) {
            return;
        }
        unset(self::$crates[$name]);
    }

    static public function loadAll():  void {
        @mkdir(BetterCrates::getInstance()->getDataFolder() . 'crates');
        $files = glob(BetterCrates::getInstance()->getDataFolder() . 'crates/*.json');
        
        foreach ($files as $file) {
            $name = basename($file, '.json');
            $config = new Config(BetterCrates::getInstance()->getDataFolder() . 'crates/' . $name . '.json', Config::JSON);
            $data = Crate::deserializeData($config->getAll());
            
            self::create($name, $data['nameFormat'], $data['textFormat'], $data['keyItem'], $data['items'], $data['blocks']);
        }
    }
    
    static public function saveAll(): void {
        @mkdir(BetterCrates::getInstance()->getDataFolder() . 'crates');

        foreach (self::getAll() as $name => $crate) {
            $config = new Config(BetterCrates::getInstance()->getDataFolder() . 'crates/' . $name . '.json', Config::JSON);
            $config->setAll($crate->serializeData());
            $config->save();
        }
    }
}