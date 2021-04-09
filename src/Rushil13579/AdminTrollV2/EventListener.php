<?php

namespace Rushil13579\AdminTrollV2;

use pocketmine\Player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\block\{
    BlockBreakEvent,
    BlockPlaceEvent,
};

use Rushil13579\AdminTrollV2\Main;

class EventListener implements Listener {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;
    }
    
    public function onMove(PlayerMoveEvent $ev){
        $player = $ev->getPlayer();

        if(isset($this->main->frozen[$player->getName()])){
            $player->setImmobile();
        }
    }

    public function onBreak(BlockBreakEvent $ev){
        $player = $ev->getPlayer();

        if(isset($this->main->noMine[$player->getName()])){
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