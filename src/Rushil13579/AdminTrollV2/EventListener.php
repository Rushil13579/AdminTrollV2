<?php

namespace Rushil13579\AdminTrollV2;

use pocketmine\Player;

use pocketmine\event\Listener;
use pocketmine\event\player\{
    PlayerItemConsumeEvent,
    PlayerCommandPreprocessEvent
};
use pocketmine\event\block\{
    BlockBreakEvent,
    BlockPlaceEvent
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

    public function onConsume(PlayerItemConsumeEvent $ev){
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

    public function onPreprocess(PlayerCommandPreprocessEvent $ev){
        $player = $ev->getPlayer();

        if(isset($this->main->trapped[$player->getName()]) or isset($this->main->voiding[$player->getName()])){
            $ev->setCancelled();
        }
    }

    public function onBreak(BlockBreakEvent $ev){
        $player = $ev->getPlayer();

        if(isset($this->main->noMine[$player->getName()]) or isset($this->main->trapped[$player->getName()])){
            $ev->setCancelled();
        }
    }

    public function onPlace(BlockPlaceEvent $ev){
        $player = $ev->getPlayer();

        if(isset($this->main->noPlace[$player->getName()])){
            $ev->setCancelled();
        }
    }
}