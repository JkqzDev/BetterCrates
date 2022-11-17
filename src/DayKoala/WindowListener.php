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

namespace DayKoala;

use pocketmine\event\Listener;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use DayKoala\inventory\SimpleWindow;
use DayKoala\inventory\utils\WindowUtils;
use DayKoala\scheduler\WindowWait;
use DayKoala\inventory\action\WindowTransaction;
use DayKoala\inventory\tile\CustomWindow;

final class WindowListener implements Listener {

    public function onOpen(InventoryOpenEvent $event): void {
      $inventory = $event->getInventory();

      if (!$inventory instanceof SimpleWindow) {
         return;
      }
      $player = $event->getPlayer();

      if (WindowWait::inWait($player)) {
         if (!WindowUtils::hasCallback($player)) {
            WindowUtils::addCallback($player);
         }
         $wait = WindowWait::getWait($player);

         if (!$wait->isCreated()) {
            $event->cancel();
         }
      } else {
         if ($event->isCancelled()) {
            return;
         }
         WindowWait::addWait($player, $inventory);
         $event->cancel();
      }
    }

   public function onClose(InventoryCloseEvent $event): void {
      $inventory = $event->getInventory();
      
      if($inventory instanceof SimpleWindow) {
         $inventory->onRemove($event->getPlayer());
      }
   }

   public function onTransaction(InventoryTransactionEvent $event): void {
      foreach($event->getTransaction()->getActions() as $action) {
         if (!$action instanceof SlotChangeAction) {
            continue;
         }
         $inventory = $action->getInventory();
         
         if (!$inventory instanceof CustomWindow) {
            continue;
         }
         $target = $action->getTargetItem();
         $source = $action->getSourceItem();
         
         if ($inventory->hasItemCallback($target)) {
            $callback = $inventory->getItemCallback($target);
            $type = WindowTransaction::TARGET_ITEM_ACTION;
         } elseif ($inventory->hasItemCallback($source)) {
            $callback = $inventory->getItemCallback($source);
            $type = WindowTransaction::SOURCE_ITEM_ACTION;
         } else {
            $callback = $inventory->getTransaction();
            $type = WindowTransaction::INVENTORY_ACTION;
         }

         if ($callback) {
            $callback(new WindowTransaction($inventory, $event->getTransaction()->getSource(), $action, $event, $type));
         }
         break;
      }
   }

   public function onQuit(PlayerQuitEvent $event): void {
      $player = $event->getPlayer();
      
      if (WindowWait::inWait($player)) {
         WindowWait::cancelWait($player);
      }
   }
}