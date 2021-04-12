<?php

namespace Rushil13579\AdminTrollV2\Tasks;

use pocketmine\Player;

use pocketmine\scheduler\Task;

use Rushil13579\AdminTrollV2\Main;

class noPlaceResetTask extends Task {

    /** @var Main */
    private $main;
    
    /** @var Player */
    private $victim;

    public function __construct(Main $main, Player $victim){
        $this->main = $main;
        $this->victim = $victim;
    }

    public function onRun($tick){
        if(isset($this->main->noPlace[$this->victim->getName()])){
            unset($this->main->noPlace[$this->victim->getName()]);
        }
    }
}