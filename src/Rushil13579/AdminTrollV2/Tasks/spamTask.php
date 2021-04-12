<?php

namespace Rushil13579\AdminTrollV2\Tasks;

use pocketmine\Player;

use pocketmine\scheduler\Task;

use Rushil13579\AdminTrollV2\Main;

class spamTask extends Task {

    /** @var Main */
    private $main;

    /** @var Player */
    private $victim;

    /** @var Int */
    private $msgcount = 0;

    public function __construct(Main $main, Player $victim){
        $this->main = $main;
        $this->victim = $victim;
    }

    public function onRun($tick){
        if($this->msgcount == 100){
            $this->main->getScheduler()->cancelTask($this->getTaskId());
        }
        $msgs = [
            '§aT⍑╎ᓭ ╎ᓭ ᓭ!¡ᔑᒲ ꖎ𝙹ꖎ𝙹ꖎ𝙹ꖎ𝙹ꖎ𝙹ꖎ𝙹ꖎ𝙹ꖎ𝙹ꖎ𝙹ꖎ𝙹ꖎ𝙹ꖎ𝙹ꖎ𝙹ꖎ𝙹ꖎ𝙹ꖎ',
            '§bY𝙹⚍ ᔑ∷ᒷ ⊣ᒷℸ ̣ ℸ ̣ ╎リ⊣ ℸ ̣ ∷𝙹ꖎꖎᒷ↸',
            '§cOリꖎ|| ᔑ ᓵ𝙹⚍!¡ꖎᒷ ᒲ𝙹∷ᒷ ℸ ̣ 𝙹 ⊣𝙹',
            '§dMᔑ↸ᒷ ʖ|| r⚍ᓭ⍑╎ꖎ. H𝙹!¡ᒷ ||𝙹⚍ ꖎ╎ꖌᒷ ╎ℸ ̣  ⍑ᒷ⍑ᒷ',
            '§fOꖌ, ℸ ̣ ⍑╎ᓭ ╎ᓭ ℸ ̣ ⍑ᒷ ꖎᔑᓭℸ ̣  𝙹⎓ ℸ ̣ ⍑ᒷ ⎓╎⍊ᒷ ᒲᒷᓭᓭᔑ⊣ᒷᓭ'
        ];
        $msg = $msgs[array_rand($msgs)];
        $this->victim->sendMessage($msg);
        $this->msgcount++;
    }
}