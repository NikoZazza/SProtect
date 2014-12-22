<?php
/* @author xionbig
 * @link http://xionbig.altervista.org/SignProtect 
 * @version 0.1.0 */

namespace SignProtect;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use SignProtect\Provider\YAMLProvider;
use SignProtect\Provider\SQLiteProvider;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class SignProtect extends PluginBase implements Listener{
    
    public $action = [], $setup;
    private $provider;
    
    public function onEnable(){
        $dataResources = $this->getDataFolder()."/resources/";
        if (!file_exists($this->getDataFolder())) @mkdir($this->getDataFolder(), 0755, true);
        if (!file_exists($dataResources)) @mkdir($dataResources, 0755, true);
                
        $this->setup = new Config($dataResources."config.yml", Config::YAML, [
            "version" => "1",
            "provider" => "YAML",
            "action" => [],
            "lastID" => 1,
        ]);
        
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
    }
    
    public function getID(){
        return $this->setup->get("lastID");        
    }
    
    public function incID(){
        $this->setup->set("lastID", $this->setup->get("lastID")+1);
        $this->setup->save();
    }
    
    public function getProvider(){
        return $this->provider;
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        if(!isset($args) || !isset($args[0])) $args[0] = "help";
        
        if(!($sender instanceof Player)) return $this->onCommandServer($sender, $args);
        
        switch(strtolower($args[0])){
            case "p":
            case "protect":
                $sender->sendMessage("[SignProtect] Touch on the Sign to which you want to put the protection.");     
                $this->action[$sender->getName()] = ["action" => "protect"]; 
                return;
            
            case "u":
            case "unprotect":
                $sender->sendMessage("[SignProtect] Touch or Remove the Sign to which you want to remove the protection");     
                $this->action[$sender->getName()] = ["action" => "unprotect"];               
                return;
            
            case "i":
            case "info":
                $sender->sendMessage("[SignProtect] Touch on the Sign on which you want to receive the information");     
                $this->action[$sender->getName()] = ["action" => "info"];                    
                return;
            
            case "?":
            case "h":
            case "help":
                $mex = ["/signp protect", "/signp unprotect", "/signp info"];
                foreach($mex as $var)
                    $sender->sendMessage(TextFormat::AQUA."[SignProtect] ".$var);     
                return;
            
        }
        $sender->sendMessage("[SignProtect] Invalid Command!");
    }
    
    public function onCommandServer(CommandSender $sender, array $args){
        switch(strtolower($args[0])){
            case "r":
            case "remove":
                if(isset($args[1]) && is_numeric($args[1])){
                    $var = $this->getProvider()->getById($args[1]);
                    if($this->getProvider()->exists($var)){
                        $this->getProvider()->remove($var);
                        $sender->sendMessage(TextFormat::GREEN."[SignProtect] The protection of the Sign has been successfully removed");
                        return;
                    }
                    $sender->sendMessage(TextFormat::RED."[SignProtect] Sign not Found!");
                    return;
                }
                break;
            case "?":
            case "h":
            case "help":
                $mex = ["/sign remove <idOfSign> Remove the Sign from Command", "/sign help"];
                foreach($mex as $var)
                    $sender->sendMessage(TextFormat::AQUA."[SignProtect] ".$var);     
                return;
        }
        $sender->sendMessage(TextFormat::RED."[SignProtect] Invalid Command!");
    }

    public function onDisable(){
        $this->setup->save();
        $this->getProvider()->onDisable();
    }
}