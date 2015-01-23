<?php
/* @author xionbig
 * @link http://xionbig.altervista.org/SProtect 
 * @link http://forums.pocketmine.net/plugins/sprotect.882/
 * @version 0.2.0 */
namespace SProtect\EventListener;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\Item;
use pocketmine\Server;

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
                if($get["maker"] == strtolower($player->getDisplayName())){  
                    $player->sendMessage("[SProtect] The Sign has been removed successfully.");
                    $this->SignMain->getProvider()->remove($var);
                }else{
                    $player->sendMessage("[SProtect] This sign is not yours!");
                    $event->setCancelled();
                    $this->SignMain->respawnSign($var);
                }
            }
        } 
    }
}