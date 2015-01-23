<?php
/* @author xionbig
 * @link http://xionbig.altervista.org/SProtect 
 * @link http://forums.pocketmine.net/plugins/sprotect.882/
 * @version 0.2.0 */
namespace SProtect\EventListener;

use pocketmine\event\Listener;
use pocketmine\event\block\SignChangeEvent;

class PlayerSignCreate implements Listener{    
    private $SignMain;
    
    public function __construct($main){
        $this->SignMain = $main;
    }
    
    public function signChangeEvent(SignChangeEvent $event){
        if($this->SignMain->getSetup()->get("auto") == false) return;  
        
        $player = $event->getPlayer();   
                       
        $world = str_replace(" ", "%", $event->getBlock()->getLevel()->getName());
        $var = (Int)$event->getBlock()->getX().":".(Int)$event->getBlock()->getY().":".(Int)$event->getBlock()->getZ().":".$world;
        
        if(!$this->SignMain->getProvider()->exists($var)){
            $this->SignMain->getProvider()->set($var, [
                "maker" => strtolower($player->getDisplayName()),
                "id" => $this->SignMain->getID(),                    
                "direction" => $event->getBlock()->getDamage(),
                "line0" => trim($event->getLine(0)),
                "line1" => trim($event->getLine(1)),
                "line2" => trim($event->getLine(2)),
                "line3" => trim($event->getLine(3))]);
            $this->SignMain->incID();           
        }else{
            if($this->SignMain->getProvider()->get($var)["maker"] == strtolower($player->getDisplayName())){
                $this->SignMain->getProvider()->set($var, [
                    "maker" => strtolower($player->getDisplayName()),
                    "id" => $this->SignMain->getID(),                    
                    "direction" => $event->getBlock()->getDamage(),
                    "line0" => trim($event->getLine(0)),
                    "line1" => trim($event->getLine(1)),
                    "line2" => trim($event->getLine(2)),
                    "line3" => trim($event->getLine(3))]);   
            }            
        }  
    }   
}