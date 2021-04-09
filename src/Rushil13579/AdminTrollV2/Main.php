<?php

namespace Rushil13579\AdminTrollV2;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\command\{
    Command,
    CommandSender
};

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\item\Item;

use pocketmine\level\Explosion;

use pocketmine\nbt\tag\StringTag;

use pocketmine\math\Vector3;

use Rushil13579\AdminTrollV2\Tasks\{
    fakeRestartTask, spamTask,
    clumsyTask, noMineTask,
    noPlaceTask
};

use jojoe77777\FormAPI\SimpleForm;

class Main extends PluginBase {

    public $frozen = [];
    public $isAlone = [];
    public $noMine = [];
    public $noPlace = [];

    const PREFIX = '§3[§bAdminTrollV2§3]';
    
    const TROLLS = [
        'fakeop',
        'fakedeop',
        'pumpkinhead',
        'burn',
        'freeze',
        'launch',
        'push',
        'spam',
        'crash',
        'badapple',
        'boom',
        'switch',
        'potatotroll',
        'fakerestart',
        'turn',
        'alone',
        'hurt',
        'starve',
        'nomine',
        'clumsy',
        'dropinv',
        'shuffle',
        'nomine',
        'noplace'
    ];

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    public function fakeRestartTask(){
        $this->getScheduler()->scheduleRepeatingTask(new fakeRestartTask($this), 20);
    }

    public function spamTask($victim){
        $this->getScheduler()->scheduleRepeatingTask(new spamTask($this, $victim), 1);
    }

    public function clumsyTask($victim){
        $this->getScheduler()->scheduleRepeatingTask(new clumsyTask($this, $victim), 5 * 20);
    }

    public function noMineTask($victim, $time){
        $this->getScheduler()->scheduleDelayedTask(new noMineTask($this, $victim), $time * 20);
    }

    public function noPlaceTask($victim, $time){
        $this->getScheduler()->scheduleDelayedTask(new noPlaceTask($this, $victim), $time * 20);
    }

    public function onCommand(CommandSender $troller, Command $cmd, String $label, Array $args) : bool {
        if(!$troller instanceof Player){
            $troller->sendMessage(self::PREFIX . ' §cPlease use this command in-game');
            return false;
        }

        if(!$troller->hasPermission('admintrollv2.*')){
            $troller->sendMessage(self::PREFIX . ' §cYou don\'t have permission to use this command');
            return false;
        }

        if($cmd->getName() == 'fakerestart'){
            $this->fakeRestartTask();
            return false;
        }

        if(!isset($args[0])){
            $troller->sendMessage(self::PREFIX . ' §cError, do /admintroll help for a list of available troll commands');
            return false;
        }

        $victim = $this->getServer()->getPlayer($args[0]);

        if($victim == null){
            $troller->sendMessage(self::PREFIX . ' Invalid Player Argument');
            return false;
        }

        if($cmd->getName() == 'admintroll'){
            $this->trollForm($troller, $victim);
            return false;
        }

        if($cmd->getName() == 'fakeop'){
            $victim->sendMessage('§7You are now op!');
            return false;
        }

        if($cmd->getName() == 'fakedeop'){
            $victim->sendMessage('§7You are no longer op!');
            return false;
        }

        if($cmd->getName() == 'pumpkinhead'){
            $pumpkin = Item::get(Item::PUMPKIN, 0, 1);
            $victim->getArmorInventory()->setHelmet($pumpkin);
            return false;
        }
  
        if($cmd->getName() == 'freeze'){
            if(isset($this->frozen[$victim->getName()])){
                unset($this->frozen[$victim->getName()]);
                $victim->setImmobile(false);
            } else {
                $this->frozen[$victim->getName()] = $victim->getName();
                $victim->setImmobile();
            }
            return false;
        }

        if($cmd->getName() == 'launch'){
            $victim->setMotion(new Vector3(0, 5, 0));
            return false;
        }

        if($cmd->getName() == 'push'){
            $victim->setMotion(new Vector3(3, 3, 3));
            return false;
        }

        if($cmd->getName() == 'spam'){
            $this->spamTask($victim);
            return false;
        }

        if($cmd->getName() == 'crash'){
            $victim->kick('§fDisconnected from Server', false);
            return false;
        }

        if($cmd->getName() == 'badapple'){
            $apple = Item::get(Item::APPLE, 0, 1);
            $apple->setCustomName('§l§4Eat Me');
            $apple->setNamedTagEntry(new StringTag('BadApple', 'BadApple'));
            $victim->getInventory()->addItem($apple);
            return false;
        }

        if($cmd->getName() == 'boom'){
            $explosion = new Explosion($victim, 0, $this);
            $explosion->explodeB();
            return false;
        }

        if($cmd->getName() == 'switch'){
            $trollerPos = $troller->getPosition();
            $troller->teleport($victim->getPosition());
            $victim->teleport($trollerPos);
            return false;
        }

        if($cmd->getName() == 'potatotroll'){
            $potato = Item::get(Item::POISONOUS_POTATO);
            $victim->getArmorInventory()->setHelmet($potato);
            $victim->getArmorInventory()->setChestplate($potato);
            $victim->getArmorInventory()->setLeggings($potato);
            $victim->getArmorInventory()->setBoots($potato);
            for($i = 0; $i < 36; $i++){
                $victim->getInventory()->setItem($i, $potato);
            }
            return false;
        }

        if($cmd->getName() == 'turn'){
            $nYaw = $victim->getYaw() + 180;
            $victim->teleport($victim->asVector3(), $nYaw);
            return false;
        }

        if($cmd->getName() == 'alone'){
            foreach($this->getServer()->getOnlinePlayers() as $player){
                if(isset($this->isAlone[$victim->getName()])){
                    unset($this->isAlone[$victim->getName()]);
                    $victim->showPlayer($player);
                } else {
                    $this->isAlone[$player->getName()] = $victim->getName();
                    if($player->getName() != $victim->getName()){
                        $victim->hidePlayer($player);
                    }
                }
            }
            return false;
        }

        if($cmd->getName() == 'clumsy'){
            $this->clumsyTask($victim);
            return false;
        }

        if($cmd->getName() == 'dropinv'){
            foreach($victim->getInventory()->getContents() as $item){
                $victim->getLevel()->dropItem($victim, $item);
                $victim->getInventory()->clearAll();
            }
            return false;
        }

        if(!isset($args[1])){
            $troller->sendMessage(self::PREFIX . ' §cError, do /admintroll help for a list of available troll commands');
            return false;
        }

        $value = $args[1];

        if(!is_numeric($value)){
            $troller->sendMessage(self::PREFIX . ' §cNon-numeric argument given. Do /admintroll help for a list of available troll commands');
            return false;
        }

        if($cmd->getName() == 'burn'){
            $victim->setOnFire((int) $value);
            return false;
        }
        
        if($cmd->getName() == 'hurt'){
            $ev = new EntityDamageEvent($victim, EntityDamageEvent::CAUSE_MAGIC, (int) $value);
            $victim->attack($ev);
            return false;
        }
        
        if($cmd->getName() == 'starve'){
            if($victim->getFood() - (int) $value < 0){
                $nFood = 0;
            } else {
                $nFood = $victim->getFood() - (int) $value;
            }
            $victim->setFood($nFood);
            return false;
        }

        if($cmd->getName() == 'nomine'){
            $this->noMine[$victim->getName()] = $victim->getName();
            $this->noMineTask($victim, (int) $value);
            return false;
        }

        if($cmd->getName() == 'noplace'){
            $this->noPlace[$victim->getName()] = $victim->getName();
            $this->noPlaceTask($victim, (int) $value);
            return false;
        }
        return true;
    }

    public function trollForm($troller, $victim){
        $form = new SimpleForm(function (Player $troller, $data = null){
            if($data === null){
                return null;
            }

            foreach(self::TROLLS as $troll){
                if($data == $troll){
                    //do stuff
                }
            }
        });
        $form->setTitle('§bAdminTrollV2: §4' . $victim); //title
        foreach(self::TROLLS as $troll){
            $form->addButton("§l§b$troll", '-1', '', $troll);
        }
        $form->sendToPlayer($troller);
        return $form;
    }
}