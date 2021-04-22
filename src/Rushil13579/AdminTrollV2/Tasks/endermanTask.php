<?php

namespace Rushil13579\AdminTrollV2\Tasks;

use pocketmine\Player;

use pocketmine\scheduler\Task;

use pocketmine\level\Position;

use pocketmine\block\Block;

use pocketmine\math\Vector3;

use Rushil13579\AdminTrollV2\Main;

class endermanTask extends Task {
    
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

    /** @var Int */
    private $range;

    /** @var Int */
    private $count;

    /** @var Int */
    private $teleports = 0;

    public function __construct(Main $main, Player $victim, Int $x, Int $y, Int $z, Int $range, Int $count){
        $this->main = $main;
        $this->victim = $victim;
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->range = $range;
        $this->count = $count;
    }

    public function onRun($tick){
        if($this->teleports == $this->count){
            $this->main->getScheduler()->cancelTask($this->getTaskId());
            return null;
        }

        if(!$this->victim->isOnline()){
            $this->main->getScheduler()->cancelTask($this->getTaskId());
            return null;
        }

        $nX = mt_rand($this->x - $this->range, $this->x + $this->range);
        $nZ = mt_rand($this->z - $this->range, $this->z + $this->range);

        $check = null;
        for($y = 5; $check == null; $y++){
            if($y >= 255){
                $this->main->getScheduler()->cancelTask($this->getTaskId());
            }
            $block1 = $this->victim->level->getBlockAt($nX, $y, $nZ);
            $block2 = $this->victim->level->getBlockAt($nX, $y + 1, $nZ);
            if($block1 instanceof Block and $block1->getId() == Block::AIR and $block2 instanceof Block and $block2->getId() == Block::AIR){
                $this->victim->teleport($this->victim->level->getBlockAt($nX, $y, $nZ));
                $check = 'location found';
                $this->teleports++;
            }
        }
    }
}