<?php
/* @author xionbig
 * @link http://xionbig.altervista.org/SProtect 
 * @link http://forums.pocketmine.net/plugins/sprotect.882/
 * @version 0.2.0 */
namespace SProtect\Provider;

use pocketmine\utils\Config;

class YAMLProvider{
    protected $database;
    
    public function __construct($SignMain){
        $this->database = new Config($SignMain->getDataFolder()."/resources/database.yml", Config::YAML);        
        if($SignMain->getSetup()->get("version") != "two"){
            foreach($this->database->getAll() as $var => $c){
                $c["direction"] = 0;
                $c["line0"] = " ";
                $c["line1"] = " ";
                $c["line2"] = " ";
                $c["line3"] = " ";
                $this->database->set($var, $c);
            }       
            $SignMain->getSetup()->set("version", "two");
            $SignMain->getSetup()->save();
        }
    }
    
    public function exists($var){
        return $this->database->exists($var);
    }
    
    public function getAll(){
        return $this->database->getAll();
    }
    
    public function get($var){
        if($this->exists($var)){
            return $this->database->get($var);    
        }
        return false;
    }
    
    public function set($var, array $data){
        $this->database->set($var, array_merge($data));
        $this->database->save();
    }
    
    public function remove($var){
        if($this->exists($var)){
            $this->database->remove($var);
            $this->database->save();
        }        
    }
    
    public function getById($id){
        if(count($this->database->getAll()) <=0) return false;
        
        foreach($this->database->getAll() as $var => $c)
            if($c["id"] == $id) return $var;   
        
        return false;
    }
    
    public function getByMaker($player){
        $player = strtolower($player);
        $return = [];
        foreach($this->getAll() as $var => $c){
            if($c["maker"] == $player)
                $return[count($return)+1] = [$var => $c];
        }
        return $return;
    }
    
    public function onDisable(){
        $this->database->save();
    }
}
