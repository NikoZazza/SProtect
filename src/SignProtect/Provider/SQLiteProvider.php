<?php
/* @author xionbig
 * @link http://xionbig.altervista.org/SignProtect 
 * @version 0.1.0 */

namespace SignProtect\Provider;

class SQLiteProvider{
    protected $database;
    
    public function __construct($SignMain){  
        if(file_exists($SignMain->getDataFolder()."/resources/database.db"))
            $this->database = new \SQLite3($SignMain->getDataFolder()."/resources/database.db", SQLITE3_OPEN_READWRITE);
        else
            $this->database = new \SQLite3($SignMain->getDataFolder()."/resources/database.db", SQLITE3_OPEN_CREATE|SQLITE3_OPEN_READWRITE);
          
        $this->database->exec('CREATE TABLE IF NOT EXISTS sign (var varchar(255), id varchar(255))');
    }
    
    public function exists($var){
        if($this->get($var) != false) 
            return true;
        return false;
    }
    
    public function get($var){
        $query = $this->database->prepare("SELECT * FROM sign WHERE var = :var");
        $query->bindValue(":var", $var, SQLITE3_TEXT);
        
        $result = $query->execute();
              
        if($result instanceof \SQLite3Result){
            $data = $result->fetchArray(SQLITE3_ASSOC);
            $result->finalize();
            if(isset($data["var"]) and $data["var"] === $var){
                $query->close();
                return $data;
            }
        }
        
        $query->close();
        return false;
    }
    
    public function set($var, array $data){
        if(!$this->exists($var))        
            $query = $this->database->prepare("INSERT INTO sign (var, id, maker) VALUES (:var, :id, :maker)");
        else
            $query = $this->database->prepare("UPDATE sign SET id = :id, maker = :maker WHERE var = :var");
        
        $query->bindValue(":var", $var, SQLITE3_TEXT);
        $query->bindValue(":id", $data["id"], SQLITE3_TEXT);
        $query->bindValue(":maker", $data["maker"], SQLITE3_TEXT);
        
        $query->execute();
        $query->close();        
    }
    
    public function remove($var){
        $query = $this->database->prepare("DELETE FROM sign WHERE var = :var");
        $query->bindValue(":var", $var, SQLITE3_TEXT);
        
        $query->execute();
        $query->close();
    }
    
    public function getById($id){
        $get = $this->getAll();
        if(count($get) <= 0) return false;
        
        foreach($get as $var => $c)
            if($c["id"] == $id) return $var;
            
        return false;
    }
    
    private function getAll(){
        $return = [];
        $query = $this->database->query("SELECT * FROM sign WHERE 1");
                
        while($data = $query->fetchArray(SQLITE3_ASSOC))
            $return[$data["var"]] = $data;
        $query->finalize();
        
        return $return;        
    }
    
    public function onDisable(){
        $this->database->close();
    }
}
