<?php
/* @author xionbig
 * @link http://xionbig.altervista.org/SProtect  
 * @link http://forums.pocketmine.net/plugins/sprotect.882/ 
 * @version 0.2.0 */
namespace SProtect;

use SProtect\Provider\YAMLProvider;
use SProtect\Provider\SQLiteProvider;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\Server;
use pocketmine\level\Position;
use pocketmine\tile\Sign;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\String;
use pocketmine\nbt\tag\Int;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\tile\Tile;

class SProtect extends PluginBase implements Listener{
    public $action = [];
    private $setup, $provider;
    
    public function onEnable(){
        $dataResources = $this->getDataFolder()."/resources/";
        if (!file_exists($this->getDataFolder())) @mkdir($this->getDataFolder(), 0755, true);
        if (!file_exists($dataResources)) @mkdir($dataResources, 0755, true);
                
        $this->setup = new Config($dataResources."config.yml", Config::YAML, [
            "version" => "two",
            "provider" => "YAML",
            "auto" => true,
            "lastID" => 1]);
        
        switch(strtolower($this->setup->get("provider"))){
            default:            
            case "yml":
            case "yaml":
                $this->provider = new YAMLProvider($this);
                break;
            case "sql":
            case "sqlite":
            case "sqlite3":
                $this->provider = new SQLiteProvider($this);
                break;
        }
        $this->getServer()->getPluginManager()->registerEvents(new EventListener\PlayerBlockBreak($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener\PlayerTouchEvent($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener\PlayerSignCreate($this), $this);
    }
    
    public function getID(){
        return $this->getSetup()->get("lastID");        
    }
    
    public function incID(){
        $this->getSetup()->set("lastID", $this->getSetup()->get("lastID")+1);
        $this->getSetup()->save();
    }
    
    public function getProvider(){
        return $this->provider;
    }
    public function getSetup(){
        return $this->setup;
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        if(!isset($args) || !isset($args[0])) $args[0] = "help";
        
        if(!($sender instanceof Player)) return $this->onCommandServer($sender, $args);
        
        switch(strtolower($args[0])){
            case "t":
            case "true":
                $sender->sendMessage("[SProtect] Touch on the Sign to which you want to put the protection.");     
                $this->action[$sender->getName()] = ["action" => "protect"]; 
                return;
            
            case "f":
            case "false":
                $sender->sendMessage("[SProtect] Touch or Remove the Sign to which you want to remove the protection");     
                $this->action[$sender->getName()] = ["action" => "unprotect"];               
                return;
            
            case "i":
            case "info":
                $sender->sendMessage("[SProtect] Touch on the Sign on which you want to receive the information");     
                $this->action[$sender->getName()] = ["action" => "info"];                    
                return;
            
            case "?":
            case "h":
            case "help":
                $mex = ["/sprotect true", "/sprotect false", "/sprotect info", "/sprotect restore"];
                foreach($mex as $var)
                    $sender->sendMessage("[SProtect] ".$var);     
                return;
                
            case "reload";
            case "restore":
                $get = $this->getProvider()->getByMaker($sender->getName());
                if(count($get)> 0){
                    foreach($get as $var => $c)
                        $this->respawnSign($var);
                    $sender->sendMessage("[SProtect] All the Signs have been replaced");    
                }else
                    $sender->sendMessage("[SProtect] There are no Signs protected by you");
                return;
        }
        if(isset($args[0]))
            $sender->sendMessage("[SProtect] Invalid command /sprotect ".$args[0]);
        else
            $sender->sendMessage("[SProtect] Invalid Command!");
    }
    
    public function onCommandServer(CommandSender $sender, array $args){
        switch(strtolower($args[0])){
            case "r":
            case "remove":
                if(isset($args[1]) && is_numeric($args[1])){
                    $var = $this->getProvider()->getById($args[1]);
                    if($this->getProvider()->exists($var)){
                        $this->getProvider()->remove($var);
                        $sender->sendMessage(TextFormat::GREEN."[SProtect] The protection of the Sign has been successfully removed");
                        return;
                    }
                    $sender->sendMessage(TextFormat::RED."[Srotect] Sign not Found!");
                    return;
                }else
                    break;
                break;
                
            case "?":
            case "h":
            case "help":
                $mex = ["/sprotect auto <true|false> ","/sprotect restore","/sprotect remove <idOfSign>"];
                foreach($mex as $var)
                    $sender->sendMessage(TextFormat::AQUA."[SProtect] ".$var);     
                return;
                
            case "auto":
                if(isset($args[1])){
                    if($args[1] == "true" || $args[1] == "on")
                        $this->getSetup()->set("auto", true);
                    else
                        $this->getSetup()->set("auto", false);
                    $sender->sendMessage(TextFormat::GREEN."[SProtect] The action has been executed successfully");
                }else
                    break;
                return;
                
            case "reload":
            case "restore":
                $get = $this->getProvider()->getAll(); 
                if(count($get > 0)){
                    foreach($get as $var => $c)
                        $this->respawnSign($var);
                    $sender->sendMessage("[SProtect] All the Signs have been replaced");    
                }else
                    $sender->sendMessage("[SProtect] There are no Signs protected");
                return;
        }
        if(isset($args[0]))
            $sender->sendMessage(TextFormat::RED."[SProtect] Invalid command /sprotect ".$args[0]);
        else
            $sender->sendMessage(TextFormat::RED."[SProtect] Invalid Command!");
    }
    
    private function spawnSign(Position $pos, $get){           
        if(!$pos->level->isChunkGenerated($pos->x, $pos->z)) $pos->level->generateChunk($pos->x, $pos->z);
        
        if($pos->level->getBlockIdAt($pos->x, $pos->y, $pos->z) != Item::SIGN_POST || $pos->level->getBlockIdAt($pos->x, $pos->y, $pos->z) != Item::SIGN)
            $pos->level->setBlock($pos, Block::get(Item::SIGN_POST, $get["direction"]), false, true);
        
        $sign = new Sign($pos->level->getChunk($pos->x >> 4, $pos->z >> 4, true), new Compound(false, array(
            new Int("x", $pos->x),
            new Int("y", $pos->y),
            new Int("z", $pos->z),
            new String("id", Tile::SIGN),
            new String("Text1", $get["line0"]),
            new String("Text2", $get["line1"]),
            new String("Text3", $get["line2"]),
            new String("Text4", $get["line3"])
            )));
        $sign->saveNBT();
        $sign->spawnToAll();
    }  
    
    public function respawnSign($var){  
        if($this->getProvider()->exists($var)){
            $g = explode(":", $var);
            if(!isset($g[3]))
                $g[3] = Server::getInstance()->getDefaultLevel()->getName();
                        
            $g[3] = str_replace("%", " ", $g[3]);
            if(Server::getInstance()->isLevelGenerated($g[3]) == true)
                $this->spawnSign(new Position($g[0], $g[1], $g[2], Server::getInstance()->getLevelByName($g[3])), $this->getProvider()->get($var));            
        }
    }

    public function onDisable(){
        $this->getProvider()->onDisable();
    }
}