<?php

namespace Rushil13579\AdminTrollV2\Tasks;

use pocketmine\Player;

use pocketmine\block\Block;
use pocketmine\level\Position;

use pocketmine\scheduler\Task;

use Rushil13579\AdminTrollV2\Main;

class noobResetTask extends Task {

    /** @var Main */
    private $main;
    
    /** @var Player */
    private $victim;

    /** @var Array */
    private $blocks;


    /** @var Position */
    private $position;

    public function __construct(Main $main, Player $victim, Array $blocks, Position $position){
        $this->main = $main;
        $this->victim = $victim;
        $this->blocks = $blocks;
        $this->position = $position;
    }

    public function onRun($tick){
        foreach ($this->blocks as $key => $block){
            if($block instanceof Block){
                $block->level->setBlock($block->asVector3(), $block);
            }
        }
        
        if(isset($this->main->noob[$this->victim->getName()])){
            unset($this->main->noob[$this->victim->getName()]);
            $this->victim->teleport($this->victim->level->getBlockAt($this->position->x, $this->position->y, $this->position->z));
            $this->victim->setImmobile(false);
            $this->victim->chat('I am a noob');
        }
    }
}