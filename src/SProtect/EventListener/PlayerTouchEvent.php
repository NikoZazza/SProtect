<?php
/* @author xionbig
 * @link http://xionbig.altervista.org/SProtect 
 * @link http://forums.pocketmine.net/plugins/sprotect.882/
 * @version 0.2.0 */
namespace SProtect\EventListener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;

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
                    $player->sendMessage("[SProtect] This Sign is already protected.");
                    return;
                }  
                
                if($this->SignMain->action[$player->getDisplayName()]["action"] == "info"){
                    $get = $this->SignMain->getProvider()->get($var);
                    $player->sendMessage("[SProtect] This Sign was created by ".$get["maker"]." and it has the id ".$get["id"]);
                    unset($this->SignMain->action[$player->getDisplayName()]);
                    return;            
                }
                if($this->SignMain->action[$player->getDisplayName()]["action"] == "unprotect"){
                    if($this->SignMain->getProvider()->get($var)["maker"] == strtolower($player->getDisplayName())){
                        $this->SignMain->getProvider()->remove($var);
                        $player->sendMessage("[SProtect] The protection of the Sign has been successfully removed");
                    }else
                        $player->sendMessage("[SProtect] This sign is not yours!");                       
                    unset($this->SignMain->action[$player->getDisplayName()]);
                    return;            
                }
                $player->sendMessage("[SProtect] This Sign is already protected.");
            }else{                
                if(isset($this->SignMain->action[$player->getDisplayName()]) && $this->SignMain->action[$player->getDisplayName()]["action"] == "protect"){
                    $tile = $event->getBlock()->getLevel()->getTile(new Vector3($event->getBlock()->getX(), $event->getBlock()->getY(), $event->getBlock()->getZ()));
                   
                    $this->SignMain->getProvider()->set($var, [
                        "maker" => strtolower($player->getDisplayName()), 
                        "id" => $this->SignMain->getID(), 
                        "direction" => $event->getBlock()->getDamage(),
                        "line0" => trim($tile->getText()[0]),
                        "line1" => trim($tile->getText()[1]),
                        "line2" => trim($tile->getText()[2]),
                        "line3" => trim($tile->getText()[3])]);
                    
                    $this->SignMain->incID();
                    $player->sendMessage("[SProtect] You have protected this Sign.");    
                    unset($this->SignMain->action[$player->getDisplayName()]);
                }
            } 
        }
    }    
}