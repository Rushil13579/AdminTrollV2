<?php

namespace Rushil13579\AdminTrollV2\Tasks;

use pocketmine\Player;

use pocketmine\scheduler\Task;

use pocketmine\math\Vector3;

use Rushil13579\AdminTrollV2\Main;

class freefallTask extends Task {
    
    /** @var Main */
    private $main;

    /** @var Player */
    private $victim;

    /** @var Int */
    private $x;

    /** @var Int */
    private $y;

    /** @var Int */
    private $z;

    public function __construct(Main $main, Player $victim){
        $this->main = $main;
        $this->victim = $victim;
        $this->x = $victim->x;
        $this->y = $victim->y;
        $this->z = $victim->z;
    }

    public function onRun($tick){
        if(!isset($this->main->freefall[$this->victim->getName()])){
            $this->main->getScheduler()->cancelTask($this->getTaskId());
            return;
        }

        if(!$this->victim->isOnline()){
            $this->main->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        
        $y = $this->victim->y;
        if($y - $this->y <= 10){
            $this->victim->teleport($this->victim->level->getBlockAt($this->x, $y + $this->main->cfg->get('freefall_troll_height'), $this->z));
        }
    }
}