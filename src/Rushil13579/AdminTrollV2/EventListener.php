<?php

namespace Rushil13579\AdminTrollV2;

use pocketmine\Player;

use pocketmine\event\Listener;
use pocketmine\event\player\{
    PlayerChatEvent,
    PlayerCommandPreprocessEvent,
    PlayerItemConsumeEvent,
    PlayerQuitEvent
};
use pocketmine\event\block\{
    BlockBreakEvent,
    BlockPlaceEvent
};
use pocketmine\event\server\{
    DataPacketSendEvent,
    DataPacketReceiveEvent
};

use pocketmine\item\Item;

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

        if(isset($this->main->trap[$player->getName()]) or isset($this->main->void[$player->getName()]) or isset($this->main->web[$player->getName()])){
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
                    $player->setHealth(0);
                }
            }
        }
    }

    public function onQuit(PlayerQuitEvent $ev){
        $player = $ev->getPlayer();

        if(isset($this->main->lag[$player->getName()])){
            unset($this->main->lag[$player->getName()]);
        }
    }

    public function onBreak(BlockBreakEvent $ev){
        if($ev->isCancelled()) return null;

        $player = $ev->getPlayer();

        if(isset($this->main->noMine[$player->getName()]) or isset($this->main->trap[$player->getName()])){
            $ev->setCancelled();
        }

        if(isset($this->main->rewind[$player->getName()])){
            $currentBlock = $ev->getBlock();
            $this->main->rewindBlockTask($player, $currentBlock);
        }
    }

    public function onPlace(BlockPlaceEvent $ev){
        if($ev->isCancelled()) return null;

        $player = $ev->getPlayer();

        if(isset($this->main->noPlace[$player->getName()])){
            $ev->setCancelled();
        }

        if(isset($this->main->rewind[$player->getName()])){
            $currentBlock = $ev->getBlockReplaced();
            $this->main->rewindBlockTask($player, $currentBlock);
        }
    }

    public function onSendPacket(DataPacketSendEvent $ev){
        $player = $ev->getPlayer();
        $packet = $ev->getPacket();

        if(isset($this->main->lag[$player->getName()])){
            $expiry = $this->main->lag[$player->getName()][0];
            if($expiry > time()){
                $ev->setCancelled();
                $this->main->lag[$player->getName()][1][] = $packet;
            } else {
                $pks = $this->main->lag[$player->getName()][1];
                unset($this->main->lag[$player->getName()]);
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

        if(isset($this->main->lag[$player->getName()])){
            $expiry = $this->main->lag[$player->getName()][0];
            if($expiry > time()){
                $ev->setCancelled();
            }
        }
    }
}