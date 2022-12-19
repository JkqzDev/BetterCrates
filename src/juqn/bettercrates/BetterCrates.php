<?php

declare(strict_types=1);

namespace juqn\bettercrates;

use DayKoala\Windowy;
use juqn\bettercrates\block\BlockFactory;
use juqn\bettercrates\command\BetterCratesCommand;
use juqn\bettercrates\command\BetterKeyCommand;
use juqn\bettercrates\crate\CrateFactory;
use juqn\bettercrates\entity\TextEntity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\world\World;

final class BetterCrates extends PluginBase {

    static private BetterCrates $instance;

    static public function getInstance(): BetterCrates {
        return self::$instance;
    }

    protected function onLoad(): void {
        self::$instance = $this;
    }

    protected function onEnable(): void {
        $this->registerWindowy();
        $this->registerCommands();
        $this->registerHandlers();
        $this->registerEntities();

        CrateFactory::loadAll();
    }

    protected function onDisable(): void {
        CrateFactory::saveAll();
    }

    private function registerWindowy(): void {
    }

    private function registerCommands(): void {
        $this->getServer()->getCommandMap()->registerAll('BetterCrates', [
            new BetterCratesCommand,
            new BetterKeyCommand
        ]);
    }

    private function registerHandlers(): void {
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler, $this);
    }

    private function registerEntities(): void {
        EntityFactory::getInstance()->register(TextEntity::class, function (World $world, CompoundTag $nbt): TextEntity {
            $entity = new TextEntity(EntityDataHelper::parseLocation($nbt, $world), $nbt);
            $entity->flagForDespawn();
            
            return $entity;
        }, ['TextEntity', 'minecraft:textentity']);
    }
}