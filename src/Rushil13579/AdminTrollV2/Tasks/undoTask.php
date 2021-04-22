<?php

namespace Rushil13579\AdminTrollV2\Tasks;

use pocketmine\block\Block;

use pocketmine\scheduler\Task;

class undoTask extends Task {

    /** @var Object */
    private $block;

    public function __construct(Object $block){
        $this->block = $block;
    }

    public function onRun($tick){
        if($this->block instanceof Block){
            $this->block->level->setBlock($this->block->asVector3(), $this->block);
        }
    }
}