<?php

namespace Rushil13579\AdminTrollV2;

use pocketmine\Player;

use pocketmine\plugin\Plugin;

use pocketmine\command\{
    Command,
    CommandSender,
    PluginIdentifiableCommand
};

use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\item\Item;
use pocketmine\block\Block;

use pocketmine\entity\{
    Entity,
    Effect,
    EffectInstance
};

use pocketmine\level\{
    Position,
    Explosion
};

use pocketmine\network\mcpe\protocol\{
    AddActorPacket,
    PlaySoundPacket,
    LevelEventPacket
};

use pocketmine\nbt\tag\StringTag;

use pocketmine\math\Vector3;

use Rushil13579\AdminTrollV2\Main;

class TrollCommand extends Command implements PluginIdentifiableCommand {

    /** @var Main */
    private $main;

    public function __construct(Main $main){
        $this->main = $main;

        parent::__construct('troll', 'Master command for AdminTrolV2', '/troll help');
        $this->setPermission('admintrollv2.all');
        $this->setPermissionMessage(Main::PREFIX . ' §cYou don\'t have permission to use this command');
    }

    public function execute(CommandSender $troller, string $commandLabel, array $args){
        if(!$this->testPermission($troller)){
            return false;
        }

        if(!isset($args[0])){
            $troller->sendMessage(Main::PREFIX . ' §cError, do /troll help for a list of available trolls');
            return false;
        }

        $troll = $args[0];

        if($troll != 'help' and !array_key_exists($troll, Main::USAGES)){
            $troller->sendMessage(Main::PREFIX . ' §cError, do /troll help for a list of available trolls');
            return false;
        }

        if($troll == 'fakerestart'){
            $this->main->fakeRestartTask();
            $troller->sendMessage(Main::PREFIX . ' §aPlayers are now receving a fake restart message!');
            return false;
        }

        if($troll == 'help'){
            $troller->sendMessage('§3<<< ' . Main::PREFIX . ' >>>');
            $array = Main::USAGES;
            foreach($array as $name => $usage){
                $troller->sendMessage('§e' . $name . ': §7' . $usage);
            }
            $troller->sendMessage('§4Descriptions: §chttps://github.com/Rushil13579/AdminTrollV2');
            return false;
        }

        if(!isset($args[1])){
            $troller->sendMessage(Main::PREFIX . ' §cError, do /troll help for a list of available trolls');
            $this->main->sendUsage($troller, $troll);
            return false;
        }

        $victim = $this->main->getServer()->getPlayer($args[1]);

        if($victim == null or !$victim->isOnline()){
            $troller->sendMessage(Main::PREFIX . ' §cInvalid Player Argument');
            $this->main->sendUsage($troller, $troll);
            return false;
        }

        if($this->main->cfg->get('immune_perm_active') == 'true'){
            if($victim->hasPermission('admintrollv2.immune')){
                $troller->sendMessage(Main::PREFIX . ' §cError, this player is immune to all trolls!');
                return false;
            }
        }

        if($troll == 'alone'){
            if(isset($this->main->alone[$victim->getName()])){
                unset($this->main->alone[$victim->getName()]);
                $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ais no longed alone!');
                foreach($this->main->getServer()->getOnlinePlayers() as $player){
                    $victim->showPlayer($player);
                }
            } else {
                $this->main->alone[$victim->getName()] = $victim->getName();
                $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ais now alone!');
                foreach($this->main->getServer()->getOnlinePlayers() as $player){
                    $victim->hidePlayer($player);
                }
            }
            return false;
        }

        if($troll == 'badapple'){
            $apple = Item::get(Item::APPLE, 0, 1);
            $apple->setCustomName('§l§4Eat Me');
            $apple->setNamedTagEntry(new StringTag('BadApple', 'BadApple'));
            $victim->getInventory()->addItem($apple);
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ahas been given a bad apple!');
            return false;
        }

        if($troll == 'bolt'){
            if($this->main->cfg->get('bolt_troll_enabled') != true){
                $troller->sendMessage(Main::PREFIX . ' §cThis troll is disabled!');
                return false;
            }
            $lightning = new AddActorPacket();
            $lightning->type = "minecraft:lightning_bolt";
            $lightning->entityRuntimeId = Entity::$entityCount++;
            $lightning->metadata = [];
            $lightning->position = $victim->asPosition();
            $lightning->yaw = 0;
            $lightning->pitch = 0;

            $sound = new PlaySoundPacket();
            $sound->x = $victim->x;
            $sound->y = $victim->y;
            $sound->z = $victim->z;
            $sound->volume = 100;
            $sound->pitch = 2;
            $sound->soundName = "ambient.weather.thunder";

            $this->main->getServer()->broadcastPacket($victim->level->getPlayers(), $lightning);
            $this->main->getServer()->broadcastPacket($victim->level->getPlayers(), $sound);
            $ev = new EntityDamageEvent($victim, EntityDamageEvent::CAUSE_CUSTOM, 5);
            $victim->attack($ev);
            if($this->main->cfg->get('bolt_troll_sets_ground_to_fire') == 'true'){
                $victim->level->setBlock($victim->asVector3(), Block::get(Block::FIRE));
            }
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ahas been struck with a lightning bolt!');
            return false;
        }

        if($troll == 'boom'){
            $explosion = new Explosion($victim, 0.1, $this);
            $explosion->explodeB();
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ais being exploded!');
            return false;
        }

        if($troll == 'clumsy'){
            $this->main->clumsyTask($victim);
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ais now clumsy!');
            return false;
        }

        if($troll == 'crash'){
            $victim->kick('§fDisconnected from Server', false);
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ahas been crashed!');
            return false;
        }

        if($troll == 'dropinv'){
            foreach($victim->getInventory()->getContents() as $item){
                $victim->level->dropItem($victim, $item);
                $victim->getInventory()->clearAll();
            }
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' \'s §ainventory has been dropped!');
            return false;
        }

        if($troll == 'drunk'){
            $victim->addEffect(new EffectInstance(Effect::getEffect(Effect::NAUSEA), 30 * 20, 255, false));
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ais now drunk!');
            return false;
        }

        if($troll == 'enderman'){
            $range = $this->main->cfg->get('enderman_troll_range');
            $count = $this->main->cfg->get('enderman_troll_count');
            $this->main->endermanTask($victim, $victim->x, $victim->y, $victim->z, $range, $count);
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ais now teleporting everywhere!');
            return false;
        }

        if($troll == 'fakeban'){
            $victim->kick('§fBanned by admin.', false);
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ahas been fake banned!');
            return false;
        }

        if($troll == 'fakedeop'){
            $victim->sendMessage('§7You are no longer op!');
            $troller->sendMessage(Main::PREFIX . ' §aSending Fake Deop message to §c' . $victim->getName() . '!');
            return false;
        }

        if($troll == 'fakeop'){
            $victim->sendMessage('§7You are now op!');
            $troller->sendMessage(Main::PREFIX . ' §aSending Fake Op message to §c' . $victim->getName() . '!');
            return false;
        }

        if($troll == 'flip'){
            $nYaw = $victim->getYaw() + 180;
            $victim->teleport($victim->asVector3(), $nYaw);
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ahas been flipped 180!');
            return false;
        }

        if($troll == 'freefall'){
            if(isset($this->main->freefall[$victim->getName()])){
                unset($this->main->freefall[$victim->getName()]);
                $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ais no longer free falling');
            } else {
                $this->main->freefall[$victim->getName()] = $victim->getName();
                $this->main->freefallTask($victim);
                $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ais now free falling');
            }
            return false;
        }

        if($troll == 'garble'){
            if(isset($this->main->garble[$victim->getName()])){
                unset($this->main->garble[$victim->getName()]);
                $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . '\'s §amessages will now make sense!');
            } else {
                $this->main->garble[$victim->getName()] = $victim->getName();
                $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . '\'s §amessages will now make no sense!');
            }
            return false;
        }

        if($troll == 'haunt'){
            $v = mt_rand(1, 3);
            if($v == 1){
                $sound = LevelEventPacket::EVENT_SOUND_ENDERMAN_TELEPORT;
            } elseif ($v == 2){
                $sound = LevelEventPacket::EVENT_SOUND_GHAST;
            } else {
                $sound = LevelEventPacket::EVENT_SOUND_BLAZE_SHOOT;
            }
            $victim->level->broadcastLevelEvent($victim->getPosition(), $sound);
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ais now hearing spooky sounds!');
            return false;
        }

        if($troll == 'launch'){
            $victim->setMotion(new Vector3(0, 5, 0));
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ahas been launched!');
            return false;
        }

        if($troll == 'noob'){
            $oldPosition = $victim->getPosition();
            if(!$oldPosition instanceof Vector3) return false;

            $victim->setImmobile();

            $x = $oldPosition->x;
            $y = $oldPosition->y;
            $z = $oldPosition->z;

            $nQuartz = [
                new Position($x + 20, 248, $z - 11),
                new Position($x + 20, 249, $z - 11),
                new Position($x + 20, 250, $z - 11),
                new Position($x + 20, 251, $z - 11),
                new Position($x + 20, 252, $z - 11),
                new Position($x + 20, 253, $z - 11),
                new Position($x + 20, 254, $z - 11),
                new Position($x + 20, 253, $z - 10),
                new Position($x + 20, 252, $z - 9),
                new Position($x + 20, 251, $z - 9),
                new Position($x + 20, 250, $z - 9),
                new Position($x + 20, 249, $z - 8),
                new Position($x + 20, 248, $z - 7),
                new Position($x + 20, 249, $z - 7),
                new Position($x + 20, 250, $z - 7),
                new Position($x + 20, 251, $z - 7),
                new Position($x + 20, 252, $z - 7),
                new Position($x + 20, 253, $z - 7),
                new Position($x + 20, 254, $z - 7)
            ];

            $nLantern = [
                new Position($x + 21, 248, $z - 11),
                new Position($x + 21, 249, $z - 11),
                new Position($x + 21, 250, $z - 11),
                new Position($x + 21, 251, $z - 11),
                new Position($x + 21, 252, $z - 11),
                new Position($x + 21, 253, $z - 11),
                new Position($x + 21, 254, $z - 11),
                new Position($x + 21, 253, $z - 10),
                new Position($x + 21, 252, $z - 9),
                new Position($x + 21, 251, $z - 9),
                new Position($x + 21, 250, $z - 9),
                new Position($x + 21, 249, $z - 8),
                new Position($x + 21, 248, $z - 7),
                new Position($x + 21, 249, $z - 7),
                new Position($x + 21, 250, $z - 7),
                new Position($x + 21, 251, $z - 7),
                new Position($x + 21, 252, $z - 7),
                new Position($x + 21, 253, $z - 7),
                new Position($x + 21, 254, $z - 7)
            ];

            $o1Quartz = [
                new Position($x + 20, 248, $z - 2),
                new Position($x + 20, 248, $z - 3),
                new Position($x + 20, 248, $z - 4),
                new Position($x + 20, 254, $z - 2),
                new Position($x + 20, 254, $z - 3),
                new Position($x + 20, 254, $z - 4),
                new Position($x + 20, 249, $z - 1),
                new Position($x + 20, 250, $z - 1),
                new Position($x + 20, 251, $z - 1),
                new Position($x + 20, 252, $z - 1),
                new Position($x + 20, 253, $z - 1),
                new Position($x + 20, 249, $z - 5),
                new Position($x + 20, 250, $z - 5),
                new Position($x + 20, 251, $z - 5),
                new Position($x + 20, 252, $z - 5),
                new Position($x + 20, 253, $z - 5)
            ];

            $o1Lantern = [
                new Position($x + 21, 248, $z - 2),
                new Position($x + 21, 248, $z - 3),
                new Position($x + 21, 248, $z - 4),
                new Position($x + 21, 254, $z - 2),
                new Position($x + 21, 254, $z - 3),
                new Position($x + 21, 254, $z - 4),
                new Position($x + 21, 249, $z - 1),
                new Position($x + 21, 250, $z - 1),
                new Position($x + 21, 251, $z - 1),
                new Position($x + 21, 252, $z - 1),
                new Position($x + 21, 253, $z - 1),
                new Position($x + 21, 249, $z - 5),
                new Position($x + 21, 250, $z - 5),
                new Position($x + 21, 251, $z - 5),
                new Position($x + 21, 252, $z - 5),
                new Position($x + 21, 253, $z - 5)
            ];

            $o2Quartz = [
                new Position($x + 20, 248, $z + 2),
                new Position($x + 20, 248, $z + 3),
                new Position($x + 20, 248, $z + 4),
                new Position($x + 20, 254, $z + 2),
                new Position($x + 20, 254, $z + 3),
                new Position($x + 20, 254, $z + 4),
                new Position($x + 20, 249, $z + 1),
                new Position($x + 20, 250, $z + 1),
                new Position($x + 20, 251, $z + 1),
                new Position($x + 20, 252, $z + 1),
                new Position($x + 20, 253, $z + 1),
                new Position($x + 20, 249, $z + 5),
                new Position($x + 20, 250, $z + 5),
                new Position($x + 20, 251, $z + 5),
                new Position($x + 20, 252, $z + 5),
                new Position($x + 20, 253, $z + 5)
            ];

            $o2Lantern = [
                new Position($x + 21, 248, $z + 2),
                new Position($x + 21, 248, $z + 3),
                new Position($x + 21, 248, $z + 4),
                new Position($x + 21, 254, $z + 2),
                new Position($x + 21, 254, $z + 3),
                new Position($x + 21, 254, $z + 4),
                new Position($x + 21, 249, $z + 1),
                new Position($x + 21, 250, $z + 1),
                new Position($x + 21, 251, $z + 1),
                new Position($x + 21, 252, $z + 1),
                new Position($x + 21, 253, $z + 1),
                new Position($x + 21, 249, $z + 5),
                new Position($x + 21, 250, $z + 5),
                new Position($x + 21, 251, $z + 5),
                new Position($x + 21, 252, $z + 5),
                new Position($x + 21, 253, $z + 5)
            ];

            $bQuartz = [
                new Position($x + 20, 248, $z + 7),
                new Position($x + 20, 249, $z + 7),
                new Position($x + 20, 250, $z + 7),
                new Position($x + 20, 251, $z + 7),
                new Position($x + 20, 252, $z + 7),
                new Position($x + 20, 253, $z + 7),
                new Position($x + 20, 254, $z + 7),
                new Position($x + 20, 248, $z + 8),
                new Position($x + 20, 251, $z + 8),
                new Position($x + 20, 254, $z + 8),
                new Position($x + 20, 248, $z + 9),
                new Position($x + 20, 251, $z + 9),
                new Position($x + 20, 254, $z + 9),
                new Position($x + 20, 248, $z + 10),
                new Position($x + 20, 251, $z + 10),
                new Position($x + 20, 254, $z + 10),
                new Position($x + 20, 249, $z + 11),
                new Position($x + 20, 250, $z + 11),
                new Position($x + 20, 252, $z + 11),
                new Position($x + 20, 253, $z + 11)
            ];

            $bLantern = [
                new Position($x + 21, 248, $z + 7),
                new Position($x + 21, 249, $z + 7),
                new Position($x + 21, 250, $z + 7),
                new Position($x + 21, 251, $z + 7),
                new Position($x + 21, 252, $z + 7),
                new Position($x + 21, 253, $z + 7),
                new Position($x + 21, 254, $z + 7),
                new Position($x + 21, 248, $z + 8),
                new Position($x + 21, 251, $z + 8),
                new Position($x + 21, 254, $z + 8),
                new Position($x + 21, 248, $z + 9),
                new Position($x + 21, 251, $z + 9),
                new Position($x + 21, 254, $z + 9),
                new Position($x + 21, 248, $z + 10),
                new Position($x + 21, 251, $z + 10),
                new Position($x + 21, 254, $z + 10),
                new Position($x + 21, 249, $z + 11),
                new Position($x + 21, 250, $z + 11),
                new Position($x + 21, 252, $z + 11),
                new Position($x + 21, 253, $z + 11)
            ];

            $currentBlocks = [];
            foreach($nQuartz as $key => $position){
                $currentBlocks[] = $victim->level->getBlock(new Vector3($position->x, $position->y, $position->z));
                $victim->level->setBlock($position, Block::get(Block::QUARTZ_BLOCK));
            }
            foreach($nLantern as $key => $position){
                $currentBlocks[] = $victim->level->getBlock(new Vector3($position->x, $position->y, $position->z));
                $victim->level->setBlock($position, Block::get(Block::SEA_LANTERN));
            }
            foreach($o1Quartz as $key => $position){
                $currentBlocks[] = $victim->level->getBlock(new Vector3($position->x, $position->y, $position->z));
                $victim->level->setBlock($position, Block::get(Block::QUARTZ_BLOCK));
            }
            foreach($o1Lantern as $key => $position){
                $currentBlocks[] = $victim->level->getBlock(new Vector3($position->x, $position->y, $position->z));
                $victim->level->setBlock($position, Block::get(Block::SEA_LANTERN));
            }
            foreach($o2Quartz as $key => $position){
                $currentBlocks[] = $victim->level->getBlock(new Vector3($position->x, $position->y, $position->z));
                $victim->level->setBlock($position, Block::get(Block::QUARTZ_BLOCK));
            }
            foreach($o2Lantern as $key => $position){
                $currentBlocks[] = $victim->level->getBlock(new Vector3($position->x, $position->y, $position->z));
                $victim->level->setBlock($position, Block::get(Block::SEA_LANTERN));
            }
            foreach($bQuartz as $key => $position){
                $currentBlocks[] = $victim->level->getBlock(new Vector3($position->x, $position->y, $position->z));
                $victim->level->setBlock($position, Block::get(Block::QUARTZ_BLOCK));
            }
            foreach($bLantern as $key => $position){
                $currentBlocks[] = $victim->level->getBlock(new Vector3($position->x, $position->y, $position->z));
                $victim->level->setBlock($position, Block::get(Block::SEA_LANTERN));
            }
            $currentBlocks[] = $victim->level->getBlock(new Vector3($x, 247, $z));
            $victim->level->setBlock(new Position($x, 247, $z), Block::get(Block::GLASS));
            $this->main->noob[$victim->getName()] = $victim->getName();
            $this->main->noobResetTask($victim, $currentBlocks, $oldPosition);
            $victim->teleport($victim->level->getBlockAt($x, 248, $z));
            $victim->sendMessage('§l§aYOU ARE A NOOB!');
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ahas realised that he is a noob!');
            return false;
        }

        if($troll == 'potatoinv'){
            $potato = Item::get(Item::POISONOUS_POTATO);
            $victim->getArmorInventory()->setHelmet($potato);
            $victim->getArmorInventory()->setChestplate($potato);
            $victim->getArmorInventory()->setLeggings($potato);
            $victim->getArmorInventory()->setBoots($potato);
            for($i = 0; $i < 36; $i++){
                $victim->getInventory()->setItem($i, $potato);
            }
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' \'s §ainventory has been filled with potatoes!');
            return false;
        }
       
        if($troll == 'pumpkinhead'){
            $pumpkin = Item::get(Item::PUMPKIN, 0, 1);
            $victim->getArmorInventory()->setHelmet($pumpkin);
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §anow has a pumpkin head!');
            return false;
        }

        if($troll == 'push'){
            $victim->setMotion(new Vector3(3, 3, 3));
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ahas been pushed!');
            return false;
        }

        if($troll == 'shuffle'){
            $array = $victim->getInventory()->getContents();
            shuffle($array);
            $i = 0;
            foreach($array as $item){
                $victim->getInventory()->setItem($i, $item);
                $i++;
            }
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' \'s §ainventory has been shuffled!');
            return false;
        }

        if($troll == 'spam'){
            $this->main->spamTask($victim);
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ais being spammed!');
            return false;
        }

        if($troll == 'swap'){
            if(!$troller instanceof Player){
                $troller->sendMessage(Main::PREFIX . ' §cPlease use this command in-game');
                return false;
            }

            $trollerPos = $troller->getPosition();
            $troller->teleport($victim->getPosition());
            $victim->teleport($trollerPos);
            $troller->sendMessage(Main::PREFIX . ' §aYou have swaped positions with §c'  . $victim->getName() . '!');
            return false;
        }

        if($troll == 'undo'){
            if(isset($this->main->undo[$victim->getName()])){
                unset($this->main->undo[$victim->getName()]);
                $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . '\'s §aactions are no longer being undo\'ed');
                return false;
            } else {
                $this->main->undo[$victim->getName()] = $victim->getName();
                $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . '\'s §aactions are now being undo\'ed');
                return false;
            }
        }

        if($troll == 'useless'){
            foreach($victim->getInventory()->getContents() as $slot => $item){
                $nItem = $item->setCustomName('§cUseless');
                $victim->getInventory()->setItem($slot, $nItem);
            }
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . '\'s §aitems have been renamed to §cUseless');
            return false;
        }

        if($troll == 'void'){
            $position = $victim->getPosition();
            if(!$position instanceof Vector3) return false;

            $x = $position->x;
            $y = $position->y;
            $z = $position->z;

            $voidBlocks = [];
            for($y = 0; $y <= $position->y; $y++){
                $voidBlocks[] = new Position($x, $y, $z);
                $voidBlocks[] = new Position($x + 1, $y, $z);
                $voidBlocks[] = new Position($x - 1, $y, $z);
                $voidBlocks[] = new Position($x, $y, $z + 1);
                $voidBlocks[] = new Position($x, $y, $z - 1);
                $voidBlocks[] = new Position($x + 1, $y, $z + 1);
                $voidBlocks[] = new Position($x + 1, $y, $z - 1);
                $voidBlocks[] = new Position($x - 1, $y, $z + 1);
                $voidBlocks[] = new Position($x - 1, $y, $z - 1);
            }

            $currentBlocks = [];
            foreach($voidBlocks as $key => $position){
                $currentBlocks[] = $victim->level->getBlock(new Vector3($position->x, $position->y, $position->z));
                $victim->level->setBlock($position, Block::get(Block::AIR));
            }
            $this->main->voidResetTask($victim, $currentBlocks);
            $this->main->void[$victim->getName()] = $victim->getName();
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ahas been sent on a one way trip to the void!');
            return false;
        }
        
        if(!isset($args[2])){
            $troller->sendMessage(Main::PREFIX . ' §cError, do /troll help for a list of available trolls');
            $this->main->sendUsage($troller, $troll);
            return false;
        }

        $value = implode(' ', array_slice($args, 2));

        if($troll == 'chat'){
            $victim->chat($value);
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ais now being forced to chat §c' . $value . '!');
            return false;
        }

        if(!is_numeric($value)){
            $troller->sendMessage(Main::PREFIX . ' §cNon-numeric argument given. Do /troll help for a list of available trolls');
            $this->main->sendUsage($troller, $troll);
            return false;
        }

        if($troll == 'burn'){
            $victim->setOnFire((int) $value);
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ais now on fire for §c' . $value . ' seconds!');
            return false;
        }

        if($troll == 'fakelag'){
            $this->main->fakeLag[$victim->getName()] = [time() + $value, []];
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ais now experiencing fakelag for §c' . $value . ' seconds!');
            return false;
        }
        
        if($troll == 'hurt'){
            $ev = new EntityDamageEvent($victim, EntityDamageEvent::CAUSE_CUSTOM, (int) $value);
            $victim->attack($ev);
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ais now being hurt!');
            return false;
        }

        if($troll == 'nomine'){
            $this->main->noMine[$victim->getName()] = $victim->getName();
            $this->main->noMineResetTask($victim, (int) $value);
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §acannot mine blocks for §c' . $value . ' seconds!');
            return false;
        }

        if($troll == 'nopick'){
            $this->main->noPick[$victim->getName()] = $victim->getName();
            $this->main->noPickResetTask($victim, (int) $value);
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §acannot pickup items for §c' . $value . ' seconds!');
            return false;
        }

        if($troll == 'noplace'){
            $this->main->noPlace[$victim->getName()] = $victim->getName();
            $this->main->noPlaceResetTask($victim, (int) $value);
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §acannot place blocks for §c' . $value . ' seconds!');
            return false;
        }
        
        if($troll == 'starve'){
            if($victim->getFood() - (int) $value < 0){
                $nFood = 0;
            } else {
                $nFood = $victim->getFood() - (int) $value;
            }
            $victim->setFood($nFood);
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ais now being starved!');
            return false;
        }

        if($troll == 'spin'){
            $this->main->spinTask($victim, $value);
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ais now spinning at a speed of §c' . $value . '!');
            return false;
        }

        if($troll == 'trap'){
            $position = $victim->getPosition();
            if(!$position instanceof Vector3) return false;

            $x = $position->x;
            $y = $position->y;
            $z = $position->z;

            $trapBlocks = [
                new Position($x, $y - 1, $z),
                new Position($x, $y, $z - 1),
                new Position($x, $y + 1, $z - 1),
                new Position($x - 1, $y, $z),
                new Position($x - 1, $y + 1, $z),
                new Position($x + 1, $y, $z),
                new Position($x + 1, $y + 1, $z),
                new Position($x, $y, $z + 1),
                new Position($x, $y + 1, $z + 1),
                new Position($x, $y + 2, $z)
            ];

            $currentBlocks = [];
            foreach($trapBlocks as $key => $position){
                $currentBlocks[] = $victim->level->getBlock(new Vector3($position->x, $position->y, $position->z));
                $victim->level->setBlock($position, Block::get(Block::GLASS));
            }
            $this->main->trapResetTask($victim, $value, $currentBlocks);
            $this->main->trap[$victim->getName()] = $victim->getName();
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ais now trapped for §c' . $value . ' seconds!');
            return false;
        }

        if($troll == 'web'){
            $position = $victim->getPosition();
            if(!$position instanceof Vector3) return false;

            $x = $position->x;
            $y = $position->y;
            $z = $position->z;

            $webBlocks = [
                new Position($x, $y, $z),
                new Position($x + 1, $y, $z),
                new Position($x -1, $y, $z),
                new Position($x, $y, $z + 1),
                new Position($x, $y, $z - 1),
                new Position($x + 1, $y, $z + 1),
                new Position($x - 1, $y, $z - 1),
                new Position($x + 1, $y, $z - 1),
                new Position($x - 1, $y, $z + 1)
            ];

            $currentBlocks = [];
            foreach($webBlocks as $key => $position){
                $currentBlocks[] = $victim->level->getBlock(new Vector3($position->x, $position->y, $position->z));
                $victim->level->setBlock($position, Block::get(Block::COBWEB));
            }
            $this->main->webResetTask($victim, $value, $currentBlocks);
            $this->main->web[$victim->getName()] = $victim->getName();
            $troller->sendMessage(Main::PREFIX . ' §c' . $victim->getName() . ' §ahas been web\'ed up for §c' . $value . ' seconds!');
            return false;
        }
    }

    public function getPlugin() : Plugin {
        return $this->main;
    }
}