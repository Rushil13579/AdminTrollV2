<?php

namespace Rushil13579\AdminTrollV2\Tasks;

use pocketmine\scheduler\Task;

use Rushil13579\AdminTrollV2\Main;

class fakeRestartTask extends Task {

    /** @var Main */
    private $main;

    /** @var Int */
    private $timer = 10;

    public function __construct(Main $main){
        $this->main = $main;
    }

    public function onRun($tick){
        if($this->timer == 10){
            $this->main->getServer()->broadcastMessage('§4[Broadcast] §bServer is restarting in 10 seconds!');
        }
        if($this->timer == 5){
            $this->main->getServer()->broadcastMessage('§4[Broadcast] §bServer is restarting in 5 seconds!');
        }
        if($this->timer == 4){
            $this->main->getServer()->broadcastMessage('§4[Broadcast] §bServer is restarting in 4 seconds!');
        }
        if($this->timer == 3){
            $this->main->getServer()->broadcastMessage('§4[Broadcast] §bServer is restarting in 3 seconds!');
        }
        if($this->timer == 2){
            $this->main->getServer()->broadcastMessage('§4[Broadcast] §bServer is restarting in 2 seconds!');
        }
        if($this->timer == 1){
            $this->main->getServer()->broadcastMessage('§4[Broadcast] §bServer is restarting in 1 second!');
        }
        if($this->timer == 0){
            $this->main->getServer()->broadcastMessage('§4[Broadcast] §bServer is restarting!');
            $this->main->getScheduler()->cancelTask($this->getTaskId());
        }
        $this->timer--;
    }
}