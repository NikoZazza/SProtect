<?php
/* @author xionbig
 * @link http://xionbig.altervista.org/SignProtect 
 * @version 0.1.0 */

namespace SignProtect\EventListener;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\Item;

class PlayerBlockBreak implements Listener{
    private $SignMain;
    
    public function __construct($main){
        $this->SignMain = $main;
    }
    
    public function playerBlockBreak(BlockBreakEvent $event){
        if($event->getBlock()->getId() == Item::SIGN || $event->getBlock()->getId() == Item::SIGN_POST){
            $player = $event->getPlayer();
            
            $world = str_replace(" ", "%", $event->getBlock()->getLevel()->getName());            
            $var = (Int)$event->getBlock()->getX().":".(Int)$event->getBlock()->getY().":".(Int)$event->getBlock()->getZ().":".$world;
            
            if($this->SignMain->getProvider()->exists($var)){
                $get = $this->SignMain->getProvider()->get($var);
                if(isset($this->SignMain->action[$player->getDisplayName()]) && $this->SignMain->action[$player->getDisplayName()]["action"] == "unprotect"){
                    if($get["maker"] == strtolower($player->getDisplayName())){
                        
                        $player->sendMessage("[SignProtect] The Sign has been removed successfully.");
                        $this->SignMain->getProvider()->remove($var);
                        unset($this->SignMain->action[$player->getDisplayName()]);
                        return;
                    }
                }
                $player->sendMessage("[SignProtect] This sign is not yours!");
                $event->setCancelled();
            }
        } 
    }
}