<?php

namespace Rushil13579\AdminTrollV2;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\Config;

use Rushil13579\AdminTrollV2\Tasks\{
    clumsyTask, endermanTask,
    fakeRestartTask, freefallTask,
    noMineResetTask, noobResetTask,
    noPickResetTask, noPlaceResetTask,
    spamTask, spinTask,
    trapResetTask, undoTask,
    voidResetTask, webResetTask
};

class Main extends PluginBase {

    public $cfg;

    public $alone = [];
    public $fakeLag = [];
    public $freefall = [];
    public $garble = [];
    public $noMine = [];
    public $noob = [];
    public $noPick = [];
    public $noPlace = [];
    public $trap = [];
    public $undo = [];
    public $void = [];
    public $web = [];

    const PREFIX = '§3[§bAdminTrollV2§3]';

    const USAGES = [
        'alone' => '/troll alone (player)',
        'badapple' => '/troll badapple (player)',
        'bolt' => '/troll bolt (player)',
        'boom' => '/troll boom (player)',
        'burn' => '/troll burn (player) [seconds...]',
        'chat' => '/troll chat (player) [message...]',
        'clumsy' => '/troll clumsy (player)',
        'crash' => '/troll crash (player)',
        'dropinv' => '/troll dropinv (player)',
        'drunk' => '/troll drunk (player)',
        'enderman' => '/troll enderman (player)',
        'fakeban' => '/troll fakeban (player)',
        'fakedeop' => '/troll fakedeop (player)',
        'fakelag' => '/troll fakelag (player) [seconds...]',
        'fakeop' => '/troll fakeop (player)',
        'flip' => '/troll flip (player)',
        'freefall' => '/troll freefall (player)',
        'garble' => '/troll garble (player)',
        'haunt' => '/troll haunt (player)',
        'hurt' => '/troll hurt (player) [damage...]',
        'launch' => '/troll launch (player)',
        'nomine' => '/troll nomine (player) [seconds...]',
        'noob' => '/troll noob (player)',
        'nopick' => '/troll nopick (player) [seconds...]',
        'noplace' => '/troll noplace (player) [seconds...]',
        'potatoinv' => '/troll potatoinv (player)',
        'pumpkinhead' => '/troll pumpkinhead (player)',
        'push' => '/troll push (player)',
        'shuffle' => '/troll shuffle (player)',
        'spam' => '/troll spam (player)',
        'spin' => '/troll spin (player) (speed) [count...]',
        'starve' => '/troll starve (player) [amount...]',
        'swap' => '/troll swap (player)',
        'trap' => '/troll trap (player) [seconds...]',
        'undo' => '/troll undo (player)',
        'useless' => '/useless (player)',
        'void' => '/troll void (player)',
        'web' => '/troll web (player) [seconds...]'
    ];

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->saveDefaultConfig();

        $this->cfg = $this->getConfig();

        $this->versionCheck();

        $this->getServer()->getCommandMap()->register('AdminTrolV2', new TrollCommand($this));
    }

    public function versionCheck(){
        if($this->cfg->get('plugin_version') != '1.2.0'){
            rename($this->getDataFolder() . 'config.yml', $this->getDataFolder() . 'old_config.yml');
            $this->saveDefaultConfig();
            $this->getLogger()->warning('§cThe configuration file for AdminTrollV2 was outdated, so it has been renamed to \'old_config.yml\' and a new configuration file has been generated');
        }
    }

    public function clumsyTask($victim){
        $this->getScheduler()->scheduleRepeatingTask(new clumsyTask($this, $victim), 5 * 20);
    }

    public function endermanTask($victim, $x, $y, $z, $range, $count){
        $this->getScheduler()->scheduleRepeatingTask(new endermanTask($this, $victim, $x, $y, $z, $range, $count), $this->cfg->get('enderman_troll_teleport_interval') * 20);
    }

    public function fakeRestartTask(){
        $this->getScheduler()->scheduleRepeatingTask(new fakeRestartTask($this), 20);
    }

    public function freefallTask($victim){
        $this->getScheduler()->scheduleRepeatingTask(new freefallTask($this, $victim), 1);
    }
    
    public function noMineResetTask($victim, $time){
        $this->getScheduler()->scheduleDelayedTask(new noMineResetTask($this, $victim), $time * 20);
    }

    public function noobResetTask($victim, $blocks, $position){
        $this->getScheduler()->scheduleDelayedTask(new noobResetTask($this, $victim, $blocks, $position), 10 * 20);
    }

    public function noPickResetTask($victim, $time){
        $this->getScheduler()->scheduleDelayedTask(new noPickResetTask($this, $victim), $time * 20);
    }

    public function noPlaceResetTask($victim, $time){
        $this->getScheduler()->scheduleDelayedTask(new noPlaceResetTask($this, $victim), $time * 20);
    }

    public function spamTask($victim){
        $this->getScheduler()->scheduleRepeatingTask(new spamTask($this, $victim), 1);
    }

    public function spinTask($victim, $speed){
        $this->getScheduler()->scheduleRepeatingTask(new spinTask($this, $victim, $speed), 1);
    }

    public function trapResetTask($victim, $time, $blocks){
        $this->getScheduler()->scheduleDelayedTask(new trapResetTask($this, $victim, $blocks), $time * 20);
    }

    public function undoTask($block){
        $this->getScheduler()->scheduleDelayedTask(new undoTask($block), $this->cfg->get('undo_troll_time') * 20);
    }

    public function voidResetTask($victim, $blocks){
        $this->getScheduler()->scheduleDelayedTask(new voidResetTask($this, $victim, $blocks), 10 * 20);
    }

    public function webResetTask($victim, $time, $blocks){
        $this->getScheduler()->scheduleDelayedTask(new webResetTask($this, $victim, $blocks), $time * 20);
    }

    public function sendUsage($troller, string $cmdName){
        $array = self::USAGES;
        if(array_key_exists($cmdName, $array)){
            $usage = ' §4Usage: ' . $array[$cmdName];
            $troller->sendMessage(self::PREFIX . $usage);
        }
    }
}