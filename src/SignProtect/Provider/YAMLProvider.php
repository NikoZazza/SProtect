<?php
/* @author xionbig
 * @link http://xionbig.altervista.org/SignProtect 
 * @version 0.1.0 */

namespace SignProtect\Provider;

use pocketmine\utils\Config;

class YAMLProvider{
    protected $database;
    
    public function __construct($SignMain){
        $this->database = new Config($SignMain->getDataFolder()."/resources/database.yml", Config::YAML);        
    }
    
    public function exists($var){
        return $this->database->exists($var);
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
    
    public function onDisable(){
        $this->database->save();
    }
}
