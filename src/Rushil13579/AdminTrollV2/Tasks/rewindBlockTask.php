<?php

namespace Rushil13579\AdminTrollV2\Tasks;

use pocketmine\Player;

use pocketmine\block\Block;

use pocketmine\scheduler\Task;

class rewindBlockTask extends Task {
    
    /** @var Player */
    private $victim;

    /** @var Object */
    private $block;

    public function __construct(Player $victim, Object $block){
        $this->victim = $victim;
        $this->block = $block;
    }

    public function onRun($tick){
        if(!isset($this->block)) return null;

        if($this->block instanceof Block){
            $this->block->level->setBlock($this->block->asVector3(), $this->block);
        }
    }
}