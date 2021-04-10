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
use pocketmine\block\Block;

use pocketmine\entity\{
    Effect,
    EffectInstance
};

use pocketmine\level\{
    Position,
    Explosion
};

use pocketmine\nbt\tag\StringTag;

use pocketmine\math\Vector3;

use Rushil13579\AdminTrollV2\Tasks\{
    fakeRestartTask, spamTask,
    clumsyTask, noMineTask,
    noPlaceTask, trapTask,
    voidTask
};

class Main extends PluginBase {

    public $frozen = [];
    public $alone = [];
    public $trapped = [];
    public $voiding = [];
    public $noMine = [];
    public $noPlace = [];

    const PREFIX = '§3[§bAdminTrollV2§3]';
    
    const TROLLS = [
        'Fake Restart',
        'Fake Op',
        'Fake Deop',
        'Pumpkin Head',
        'No Move',
        'Launch',
        'Push',
        'Spam',
        'Crash',
        'Bad Apple',
        'Boom',
        'Switch',
        'Potato Inv',
        'Turn',
        'Alone',
        'Clumsy',
        'Drop Inv',
        'Shuffle',
        'Drunk',
        'Void',
        'Chat',
        'Burn',
        'Hurt',
        'Starve',
        'No Mine',
        'No Place',
        'Trap'
    ];

    const USAGES = [
        'fakeop' => '/fakeop <player>',
        'fakedeop' => '/fakedrop <player>',
        'pumpkinhead' => 'pumpkinhead <player>',
        'nomove' => '/nomove <player>',
        'launch' => '/launch <player>',
        'push' => '/push <player>',
        'spam' => '/spam <player>',
        'crash' => '/crash <player>',
        'badapple' => '/badapple <player>',
        'boom' => '/boom <player>',
        'switch' => '/switch <player>',
        'potatoinv' => '/potatoinv <player>',
        'turn' => '/turn <player>',
        'alone' => '/alone <player>',
        'clumsy' => '/clumsy <player>',
        'dropinv' => '/dropinv <player>',
        'shuffle' => '/shuffle <player>',
        'drunk' => '/drunk <player>',
        'void' => '/void <player>',
        'chat' => '/chat <player> [message...]',
        'burn' => '/burn <player> <seconds>',
        'hurt' => '/hurt <player> <damage>',
        'starve' => '/starve <player> <amount>',
        'nomine' => '/nomine <player> <seconds>',
        'noplace' => '/noplace <player> <seconds>',
        'trap' => '/trap <player> <seconds>'
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

    public function trapTask($victim, $time, $blocks){
        $this->getScheduler()->scheduleDelayedTask(new trapTask($this, $victim, $blocks), $time * 20);
    }

    public function voidTask($victim, $blocks){
        $this->getScheduler()->scheduleDelayedTask(new voidTask($this, $victim, $blocks), 10 * 20);
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

        if($cmd->getName() == 'trollhelp'){
            $troller->sendMessage('§3<<< ' . self::PREFIX . ' >>>');
            $array = self::USAGES;
            foreach($array as $name => $usage){
                $troller->sendMessage('§e' . $name . ': §7' . $usage);
            }
            return false;
        }

        if($cmd->getName() == 'fakerestart'){
            $this->fakeRestartTask();
            $troller->sendMessage(self::PREFIX . ' §aPlayers are now receving a fake restart message!');
            return false;
        }

        if(!isset($args[0])){
            $troller->sendMessage(self::PREFIX . ' §cError, do /trollhelp for a list of available troll commands');
            $this->sendUsage($troller, $cmd->getName());
            return false;
        }

        $victim = $this->getServer()->getPlayer($args[0]);

        if($victim == null){
            $troller->sendMessage(self::PREFIX . ' Invalid Player Argument');
            $this->sendUsage($troller, $cmd->getName());
            return false;
        }

        if($cmd->getName() == 'fakeop'){
            $victim->sendMessage('§7You are now op!');
            $troller->sendMessage(self::PREFIX . ' §aSending Fake Op message to §c' . $victim->getName() . '!');
            return false;
        }

        if($cmd->getName() == 'fakedeop'){
            $victim->sendMessage('§7You are no longer op!');
            $troller->sendMessage(self::PREFIX . ' §aSending Fake Deop message to §c' . $victim->getName() . '!');
            return false;
        }

        if($cmd->getName() == 'pumpkinhead'){
            $pumpkin = Item::get(Item::PUMPKIN, 0, 1);
            $victim->getArmorInventory()->setHelmet($pumpkin);
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §anow has a pumpkin head!');
            return false;
        }
  
        if($cmd->getName() == 'nomove'){
            if(isset($this->frozen[$victim->getName()])){
                unset($this->frozen[$victim->getName()]);
                $victim->setImmobile(false);
                $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ahas been allowed to move!');
            } else {
                $this->frozen[$victim->getName()] = $victim->getName();
                $victim->setImmobile();
                $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ahas been prevented from moving!');
            }
            return false;
        }

        if($cmd->getName() == 'launch'){
            $victim->setMotion(new Vector3(0, 5, 0));
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ahas been launched!');
            return false;
        }

        if($cmd->getName() == 'push'){
            $victim->setMotion(new Vector3(3, 3, 3));
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ahas been pushed!');
            return false;
        }

        if($cmd->getName() == 'spam'){
            $this->spamTask($victim);
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ais being spammed!');
            return false;
        }

        if($cmd->getName() == 'crash'){
            $victim->kick('§fDisconnected from Server', false);
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ahas been crashed!');
            return false;
        }

        if($cmd->getName() == 'badapple'){
            $apple = Item::get(Item::APPLE, 0, 1);
            $apple->setCustomName('§l§4Eat Me');
            $apple->setNamedTagEntry(new StringTag('BadApple', 'BadApple'));
            $victim->getInventory()->addItem($apple);
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ahas been given a bad apple!');
            return false;
        }

        if($cmd->getName() == 'boom'){
            $explosion = new Explosion($victim, 0.1, $this);
            $explosion->explodeB();
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ais being exploded!');
            return false;
        }

        if($cmd->getName() == 'switch'){
            $trollerPos = $troller->getPosition();
            $troller->teleport($victim->getPosition());
            $victim->teleport($trollerPos);
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ahas switched positions with you!');
            return false;
        }

        if($cmd->getName() == 'potatoinv'){
            $potato = Item::get(Item::POISONOUS_POTATO);
            $victim->getArmorInventory()->setHelmet($potato);
            $victim->getArmorInventory()->setChestplate($potato);
            $victim->getArmorInventory()->setLeggings($potato);
            $victim->getArmorInventory()->setBoots($potato);
            for($i = 0; $i < 36; $i++){
                $victim->getInventory()->setItem($i, $potato);
            }
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' \'s §ainventory has been filled with potatoes!');
            return false;
        }

        if($cmd->getName() == 'turn'){
            $nYaw = $victim->getYaw() + 180;
            $victim->teleport($victim->asVector3(), $nYaw);
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ahas been turned 180!');
            return false;
        }

        if($cmd->getName() == 'alone'){
            foreach($this->getServer()->getOnlinePlayers() as $player){
                if(isset($this->alone[$victim->getName()])){
                    unset($this->alone[$victim->getName()]);
                    $victim->showPlayer($player);
                    $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ais no longed alone!');
                } else {
                    $this->alone[$player->getName()] = $victim->getName();
                    $victim->hidePlayer($player);
                    $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ais now alone!');
                }
            }
            return false;
        }

        if($cmd->getName() == 'clumsy'){
            $this->clumsyTask($victim);
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ais now clumsy!');
            return false;
        }

        if($cmd->getName() == 'dropinv'){
            foreach($victim->getInventory()->getContents() as $item){
                $victim->getLevel()->dropItem($victim, $item);
                $victim->getInventory()->clearAll();
            }
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' \'s §ainventory has been dropped!');
            return false;
        }

        if($cmd->getName() == 'shuffle'){
            $array = $victim->getInventory()->getContents();
            shuffle($array);
            $i = 0;
            foreach($array as $item){
                $victim->getInventory()->setItem($i, $item);
                $i++;
            }
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' \'s §ainventory has been shuffled!');
            return false;
        }

        if($cmd->getName() == 'drunk'){
            $victim->addEffect(new EffectInstance(Effect::getEffect(Effect::NAUSEA), 30 * 20, 255, false));
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ais now drunk!');
            return false;
        }

        if($cmd->getName() == 'void'){
            $level = $victim->getLevel();
            $position = $victim->getPosition();
            if(!$position instanceof Vector3){
                return false;
            }

            $x = $position->getX();
            $y = $position->getY();
            $z = $position->getZ();

            $voidBlocks = [];
            for($y = $position->getY(); $y >= 0; $y--){
                $voidBlocks[] = new Position($x, $y, $z);
            }
            for($y = $position->getY(); $y >= 0; $y--){
                $voidBlocks[] = new Position($x + 1, $y, $z);
            }
            for($y = $position->getY(); $y >= 0; $y--){
                $voidBlocks[] = new Position($x - 1, $y, $z);
            }
            for($y = $position->getY(); $y >= 0; $y--){
                $voidBlocks[] = new Position($x, $y, $z + 1);
            }
            for($y = $position->getY(); $y >= 0; $y--){
                $voidBlocks[] = new Position($x, $y, $z - 1);
            }
            for($y = $position->getY(); $y >= 0; $y--){
                $voidBlocks[] = new Position($x + 1, $y, $z + 1);
            }
            for($y = $position->getY(); $y >= 0; $y--){
                $voidBlocks[] = new Position($x + 1, $y, $z - 1);
            }
            for($y = $position->getY(); $y >= 0; $y--){
                $voidBlocks[] = new Position($x - 1, $y, $z + 1);
            }
            for($y = $position->getY(); $y >= 0; $y--){
                $voidBlocks[] = new Position($x - 1, $y, $z - 1);
            }

            $currentBlocks = [];
            foreach($voidBlocks as $key => $position){
                $currentBlocks[] = $level->getBlock(new Vector3($position->getX(), $position->getY(), $position->getZ()));
                $level->setBlock($position, Block::get(Block::AIR));
            }
            $this->voidTask($victim, $currentBlocks);
            $this->voiding[$victim->getName()] = $victim->getName();
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ahas been sent on a one way trip to the void!');
            return false;
        }

        if(!isset($args[1])){
            $troller->sendMessage(self::PREFIX . ' §cError, do /admintroll help for a list of available troll commands');
            $this->sendUsage($troller, $cmd->getName());
            return false;
        }

        $value = implode(' ', array_slice($args, 1));

        if($cmd->getName() == 'chat'){
            $victim->chat($value);
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ais now being forced to chat!');
            return false;
        }

        if(!is_numeric($value)){
            $troller->sendMessage(self::PREFIX . ' §cNon-numeric argument given. Do /admintroll help for a list of available troll commands');
            $this->sendUsage($troller, $cmd->getName());
            return false;
        }

        if($cmd->getName() == 'burn'){
            $victim->setOnFire((int) $value);
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ais now on fire!');
            return false;
        }
        
        if($cmd->getName() == 'hurt'){
            $ev = new EntityDamageEvent($victim, EntityDamageEvent::CAUSE_CUSTOM, (int) $value);
            $victim->attack($ev);
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ais now being hurt!');
            return false;
        }
        
        if($cmd->getName() == 'starve'){
            if($victim->getFood() - (int) $value < 0){
                $nFood = 0;
            } else {
                $nFood = $victim->getFood() - (int) $value;
            }
            $victim->setFood($nFood);
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ais now being starved!');
            return false;
        }

        if($cmd->getName() == 'nomine'){
            $this->noMine[$victim->getName()] = $victim->getName();
            $this->noMineTask($victim, (int) $value);
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §acan no longer mine blocks!');
            return false;
        }

        if($cmd->getName() == 'noplace'){
            $this->noPlace[$victim->getName()] = $victim->getName();
            $this->noPlaceTask($victim, (int) $value);
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §acan no longer place blocks!');
            return false;
        }

        if($cmd->getName() == 'trap'){
            $level = $victim->getLevel();
            $position = $victim->getPosition();
            if(!$position instanceof Vector3){
                return false;
            }

            $x = $position->getX();
            $y = $position->getY();
            $z = $position->getZ();

            $trapBlocks = [
                new Position($x, $y - 1, $z),
                new Position($x, $y + 2, $z),
                new Position($x, $y, $z - 1),
                new Position($x, $y + 1, $z - 1),
                new Position($x - 1, $y, $z),
                new Position($x - 1, $y + 1, $z),
                new Position($x + 1, $y, $z),
                new Position($x + 1, $y + 1, $z),
                new Position($x, $y, $z + 1),
                new Position($x, $y + 1, $z + 1)
            ];

            $currentBlocks = [];
            foreach($trapBlocks as $key => $position){
                $currentBlocks[] = $level->getBlock(new Vector3($position->getX(), $position->getY(), $position->getZ()));
                $level->setBlock($position, Block::get(Block::GLASS));
            }
            $this->trapTask($victim, $value, $currentBlocks);
            $this->trapped[$victim->getName()] = $victim->getName();
            $troller->sendMessage(self::PREFIX . ' §c' . $victim->getName() . ' §ais now trapped!');
            return false;
        }
        return true;
    }

    public function sendUsage(Player $troller, string $cmdName){
        $array = self::USAGES;
        if(array_key_exists($cmdName, $array)){
            $usage = ' §4Usage: ' . $array[$cmdName];
            $troller->sendMessage(self::PREFIX . $usage);
        }
    }
}