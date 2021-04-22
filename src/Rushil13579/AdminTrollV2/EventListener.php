<?php

namespace Rushil13579\AdminTrollV2;

use pocketmine\Player;

use pocketmine\event\Listener;
use pocketmine\event\player\{
    PlayerChatEvent,
    PlayerCommandPreprocessEvent,
    PlayerItemConsumeEvent,
    PlayerMoveEvent,
    PlayerQuitEvent
};
use pocketmine\event\block\{
    BlockBreakEvent,
    BlockPlaceEvent
};
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\server\{
    DataPacketSendEvent,
    DataPacketReceiveEvent
};

use pocketmine\item\Item;
use pocketmine\inventory\PlayerInventory;

use pocketmine\nbt\tag\StringTag;

use Rushil13579\AdminTrollV2\Main;

class EventListener implements Listener {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;
    }

    public function onChat(PlayerChatEvent $ev){
        if($ev->isCancelled()) return null;

        $player = $ev->getPlayer();

        if(isset($this->main->garble[$player->getName()])){
            $array = explode(' ', $ev->getMessage());
            shuffle($array);
            $msg = implode(' ', $array);
            $ev->setMessage($msg);
        }
    }

    public function onPreprocess(PlayerCommandPreprocessEvent $ev){
        if($ev->isCancelled()) return null;

        $player = $ev->getPlayer();

        if(isset($this->main->noob[$player->getName()]) or isset($this->main->trap[$player->getName()]) or isset($this->main->void[$player->getName()]) or isset($this->main->web[$player->getName()]) or isset($this->main->freefall[$player->getName()])){
            $ev->setCancelled();
        }
    }

    public function onConsume(PlayerItemConsumeEvent $ev){
        if($ev->isCancelled()) return null;

        $player = $ev->getPlayer();
        $item = $ev->getItem();

        if($item->getId() == Item::APPLE and $item->getCustomName() == '§l§4Eat Me'){
            if($item->getNamedTag()->hasTag('BadApple')){
                if($item->getNamedTag()->getString('BadApple') == 'BadApple'){
                    $player->getInventory()->setItemInHand(Item::get(Item::AIR));
                    $player->setHealth(0);
                }
            }
        }
    }

    public function onMove(PlayerMoveEvent $ev){
        if($ev->isCancelled()) return null;

        $player = $ev->getPlayer();

        if(isset($this->main->noob[$player->getName()])){
            $player->setImmobile();
        }
    }

    public function onQuit(PlayerQuitEvent $ev){
        $player = $ev->getPlayer();

        if(isset($this->main->alone[$player->getName()])){
            unset($this->main->alone[$player->getName()]);
        }

        if(isset($this->main->freefall[$player->getName()])){
            unset($this->main->freefall[$player->getName()]);
        }

        if(isset($this->main->garble[$player->getName()])){
            unset($this->main->garble[$player->getName()]);
        }

        if(isset($this->main->fakeLag[$player->getName()])){
            unset($this->main->fakeLag[$player->getName()]);
        }

        if(isset($this->main->noMine[$player->getName()])){
            unset($this->main->noMine[$player->getName()]);
        }

        if(isset($this->main->noob[$player->getName()])){
            unset($this->main->noob[$player->getName()]);
        }

        if(isset($this->main->noPick[$player->getName()])){
            unset($this->main->noPick[$player->getName()]);
        }

        if(isset($this->main->noPlace[$player->getName()])){
            unset($this->main->noPlace[$player->getName()]);
        }

        if(isset($this->main->trap[$player->getName()])){
            unset($this->main->trap[$player->getName()]);
        }

        if(isset($this->main->undo[$player->getName()])){
            unset($this->main->undo[$player->getName()]);
        }

        if(isset($this->main->void[$player->getName()])){
            unset($this->main->void[$player->getName()]);
        }

        if(isset($this->main->web[$player->getName()])){
            unset($this->main->web[$player->getName()]);
        }
    }

    public function onBreak(BlockBreakEvent $ev){
        if($ev->isCancelled()) return null;

        $player = $ev->getPlayer();

        if(isset($this->main->noMine[$player->getName()]) or isset($this->main->noob[$player->getName()]) or isset($this->main->trap[$player->getName()]) or isset($this->main->web[$player->getName()])){
            $ev->setCancelled();
        }

        if(isset($this->main->undo[$player->getName()])){
            $currentBlock = $ev->getBlock();
            $this->main->undoTask($currentBlock);
        }
    }

    public function onPlace(BlockPlaceEvent $ev){
        if($ev->isCancelled()) return null;

        $player = $ev->getPlayer();

        if(isset($this->main->noPlace[$player->getName()])){
            $ev->setCancelled();
        }

        if(isset($this->main->undo[$player->getName()])){
            $currentBlock = $ev->getBlockReplaced();
            $this->main->undoTask($currentBlock);
        }
    }

    public function onPickupItem(InventoryPickupItemEvent $ev){
        if($ev->isCancelled()) return null;

        if(!$ev->getInventory() instanceof PlayerInventory) return null;

        $player = $ev->getInventory()->getHolder();

        if(isset($this->main->noPick[$player->getName()])){
            $ev->setCancelled();
        }
    }

    public function onSendPacket(DataPacketSendEvent $ev){
        $player = $ev->getPlayer();
        $packet = $ev->getPacket();

        if(isset($this->main->fakeLag[$player->getName()])){
            $expiry = $this->main->fakeLag[$player->getName()][0];
            if($expiry > time()){
                $ev->setCancelled();
                $this->main->fakeLag[$player->getName()][1][] = $packet;
            } else {
                $pks = $this->main->fakeLag[$player->getName()][1];
                unset($this->main->fakeLag[$player->getName()]);
                foreach($pks as $pk){
                    if($pk->isEncoded){
                        $pk->decode();
                    }
                    $player->sendDataPacket($pk);
                }
            }
        }
    }

    public function onReceivePacket(DataPacketReceiveEvent $ev){
        $player = $ev->getPlayer();

        if(isset($this->main->fakeLag[$player->getName()])){
            $expiry = $this->main->fakeLag[$player->getName()][0];
            if($expiry > time()){
                $ev->setCancelled();
            }
        }
    }
}