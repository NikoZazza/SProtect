<?php
/* @author xionbig
 * @link http://xionbig.altervista.org/SProtect
 * @link http://forums.pocketmine.net/plugins/sprotect.882/ 
 * @version 0.2.0 */
namespace SProtect\Provider;

class SQLiteProvider{
    protected $database;
    
    public function __construct($SignMain){  
        if($SignMain->getSetup()->get("version") != "two"){
            rename($SignMain->getDataFolder()."/resources/database.db", $SignMain->getDataFolder()."/resources/backup_database.db");
            $SignMain->getSetup()->set("version", "two");
            $SignMain->getSetup()->save();            
        }        
        if(file_exists($SignMain->getDataFolder()."/resources/database.db"))
            $this->database = new \SQLite3($SignMain->getDataFolder()."/resources/database.db", SQLITE3_OPEN_READWRITE);
        else
            $this->database = new \SQLite3($SignMain->getDataFolder()."/resources/database.db", SQLITE3_OPEN_CREATE|SQLITE3_OPEN_READWRITE);
          
        $this->database->exec('CREATE TABLE IF NOT EXISTS sign (var varchar(255), id varchar(255), direction varchar(11), maker varchar(255), line0 varchar(255), line1 varchar(255), line2 varchar(255), line3 varchar(255))');
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
            $query = $this->database->prepare("INSERT INTO sign (var, id, maker, direction, line0, line1, line2, line3) VALUES (:var, :id, :maker, :direction, :line0, :line1, :line2, :line3)");
        else
            $query = $this->database->prepare("UPDATE sign SET id = :id, maker = :maker, direction = :direction, line0 = :line0, line1 = :line1, line2 = :line2, line3 = :line3 WHERE var = :var");
   
        $query->bindValue(":var", $var, SQLITE3_TEXT);
        $query->bindValue(":id", $data["id"], SQLITE3_TEXT);
        $query->bindValue(":maker", $data["maker"], SQLITE3_TEXT);
        $query->bindValue(":direction", $data["direction"], SQLITE3_TEXT);
        $query->bindValue(":line0", $data["line0"], SQLITE3_TEXT);
        $query->bindValue(":line1", $data["line1"], SQLITE3_TEXT);
        $query->bindValue(":line2", $data["line2"], SQLITE3_TEXT);
        $query->bindValue(":line3", $data["line3"], SQLITE3_TEXT);
        
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
        
    public function getByMaker($player){
        $player = strtolower($player);
        $return = [];
        foreach($this->getAll() as $var => $c){
            if($c["maker"] == $player)
                $return[count($return)+1] = [$var => $c];
        }
        return $return;
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
