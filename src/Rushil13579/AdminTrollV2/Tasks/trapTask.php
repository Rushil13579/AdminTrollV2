<?php

namespace Rushil13579\AdminTrollV2\Tasks;

use pocketmine\Player;

use pocketmine\block\Block;

use pocketmine\scheduler\Task;

use Rushil13579\AdminTrollV2\Main;

class trapTask extends Task {

    /** @var Main */
    private $main;
    
    /** @var Player */
    private $victim;

    /** @var Array */
    private $blocks;

    public function __construct(Main $main, Player $victim, Array $blocks){
        $this->main = $main;
        $this->victim = $victim;
        $this->blocks = $blocks;
    }

    public function onRun($tick){
        if(empty($this->blocks)) return;
        foreach ($this->blocks as $key => $block){
            if($block instanceof Block){
                $level = $block->getLevel();
                $level->setBlock($block->asVector3(), $block);
            }
        }
        if(isset($this->main->trapped[$this->victim->getName()])){
            unset($this->main->trapped[$this->victim->getName()]);
        }
    }
}