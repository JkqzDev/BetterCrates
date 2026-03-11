<?php

declare(strict_types=1);

namespace juqn\bettercrates\entity;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;

final class TextEntity extends Entity {

    public static function getNetworkTypeId(): string {
        return EntityIds::NPC;
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(0.0, 0.0);
    }

    public function initEntity(CompoundTag $nbt): void {
        parent::initEntity($nbt);

        /*if ($this->crateName !== null && $nbt->getTag('crate_name') !== null) {
            $crate = CrateFactory::get($nbt->getString('crate_name'));
            $block = BlockFactory::get(Position::fromObject($this->getPosition()->subtract(0.5, 1.3, 0.5), $this->getPosition()->getWorld()));

            if ($crate !== null && $block !== null) {
                $block->setText($this);
                $this->setNameTag(TextFormat::colorize($crate->getTextFormat()));
            }
        }*/
        $this->setScale(0.0001);
		$this->setNoClientPredictions();

        $this->setNameTagVisible();
        $this->setNameTagAlwaysVisible();

        $this->getNetworkProperties()->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, 0.0);
        $this->getNetworkProperties()->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0.0);
    }

	protected function getInitialDragMultiplier() : float {
		return 0.0;
	}

	protected function getInitialGravity() : float {
		return 0.0;
	}
}