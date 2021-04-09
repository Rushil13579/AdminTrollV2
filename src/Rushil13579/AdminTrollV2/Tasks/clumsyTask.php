<?php

namespace Rushil13579\AdminTrollV2\Tasks;

use pocketmine\Player;

use pocketmine\scheduler\Task;

use Rushil13579\AdminTrollV2\Main;

class clumsyTask extends Task {

    /** @var Main */
    private $main;

    /** @var Player */
    private $victim;

    public $count = 0;

    public function __construct(Main $main, Player $victim){
        $this->main = $main;
        $this->victim = $victim;
    }

    public function onRun($tick){
        if($this->count == 10){
            $this->main->getScheduler()->cancelTask($this->getTaskId());
        }
        $v = mt_rand(0, 35);
        foreach($this->victim->getInventory()->getContents() as $index => $item){
            if($index == $v){
                $this->victim->getInventory()->removeItem($item);
                $this->victim->getLevel()->dropItem($this->victim, $item);
                $this->count++;
                break;
            }
        }
    }
}