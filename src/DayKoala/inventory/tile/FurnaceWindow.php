<?php

/*
 *  __          ___           _                     
 *  \ \        / (_)         | |                    
 *   \ \  /\  / / _ _ __   __| | _____      ___   _ 
 *    \ \/  \/ / | | '_ \ / _` |/ _ \ \ /\ / / | | |
 *     \  /\  /  | | | | | (_| | (_) \ V  V /| |_| |
 *      \/  \/   |_|_| |_|\__,_|\___/ \_/\_/  \__, |
 *                                             __/ |
 *                                            |___/ 
 *  @author DayKoala
 *  @link https://github.com/DayKoala/Windowy
 *  @social https://twitter.com/DayKoala
 * 
 */

namespace DayKoala\inventory\tile;

use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\ContainerSetDataPacket;

class FurnaceWindow extends CustomWindow {

    public function setSmeltTime(int $time): void {
        if ($this->holder instanceof Player) {
           $this->holder->getNetworkSession()->getInvManager()->syncData($this, ContainerSetDataPacket::PROPERTY_FURNACE_SMELT_PROGRESS, $time);
        }
    }

    public function setMaxFuelTime(int $time): void {
        if ($this->holder instanceof Player) {
           $this->holder->getNetworkSession()->getInvManager()->syncData($this, ContainerSetDataPacket::PROPERTY_FURNACE_MAX_FUEL_TIME, $time);
        }
    }

    public function setRemaningFuelTime(int $time): void {
        if ($this->holder instanceof Player) {
           $this->holder->getNetworkSession()->getInvManager()->syncData($this, ContainerSetDataPacket::PROPERTY_FURNACE_REMAINING_FUEL_TIME, $time);
        }
    }
}