<?php
/* @author xionbig
 * @link http://xionbig.altervista.org/SignProtect 
 * @version 0.1.0 */

namespace SignProtect\EventListener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;

class PlayerTouchEvent implements Listener{
    private $SignMain;

    public function __construct($main){
        $this->SignMain = $main;
    }
    
    public function playerBlockTouch(PlayerInteractEvent $event){
        if($event->getBlock()->getID() == Item::SIGN || $event->getBlock()->getID() == Item::SIGN_POST){
            $player = $event->getPlayer();   
                       
            $world = str_replace(" ", "%", $event->getBlock()->getLevel()->getName());
            $var = (Int)$event->getBlock()->getX().":".(Int)$event->getBlock()->getY().":".(Int)$event->getBlock()->getZ().":".$world;
            
            if($this->SignMain->getProvider()->exists($var)){
                if(!isset($this->SignMain->action[$player->getDisplayName()])){
                    $player->sendMessage("[SignProtect] This Sign is already protected.");
                    return;
                }  
                
                if($this->SignMain->action[$player->getDisplayName()]["action"] == "info"){
                    $get = $this->SignMain->getProvider()->get($var);
                    $player->sendMessage("[SignProtect] This Sign was created by ".$get["maker"]." and it has the id ".$get["id"]);
                    unset($this->SignMain->action[$player->getDisplayName()]);
                    return;            
                }
                if($this->SignMain->action[$player->getDisplayName()]["action"] == "unprotect"){
                    if($this->SignMain->getProvider()->get($var)["maker"] == strtolower($player->getDisplayName())){
                        $this->SignMain->getProvider()->remove($var);
                        $player->sendMessage("[SignProtect] The protection of the Sign has been successfully removed");
                    }else
                        $player->sendMessage("[SignProtect] This sign is not yours!");                       
                    unset($this->SignMain->action[$player->getDisplayName()]);
                    return;            
                }
                $player->sendMessage("[SignProtect] This Sign is already protected.");
            }else{                
                if(isset($this->SignMain->action[$player->getDisplayName()]) && $this->SignMain->action[$player->getDisplayName()]["action"] == "protect"){
                    $this->SignMain->getProvider()->set($var, ["maker" => strtolower($player->getDisplayName()), "id" => $this->SignMain->getID()]);
                    $this->SignMain->incID();
                    $player->sendMessage("[SignProtect] You have protected this Sign.");    
                    unset($this->SignMain->action[$player->getDisplayName()]);
                }
            } 
        }
    }    
}