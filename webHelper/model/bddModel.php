<?php
class bddModel extends appModel implements daoInterface{
	//Instance	
	protected $table;
	protected $otpions;
	protected $connection = "default";
	
	
	public function bddModel(){
	
	}
	
	public function get($id){
		try{
			$statement = bddModel::getConnect($this->connection)->prepare("SELECT * from ".$this->table." WHERE id=".$id);
			Logger::tracerErreur("SELECT * from ".$this->table." WHERE id=".$id);
			$this->execute($statement);
			if($statement->rowCount() != 1)  return ; //cas derreur TODO Exception
        	return $statement->fetch(PDO::FETCH_BOTH );
        
		}catch(PDOException $e){
			Logger::tracerErreur("Erreur connex");
			return false;
		}		
		
		
	}
	public function getList($hashMap_conditions = "", $options = array(),$order = array(), $limit=""){
		try{
			if($limit!="")$limit = ' LIMIT '.$limit;
			$where = $this->returnWhere($options);	
			$orderBy = $this->returnOrder($order);
			Logger::tracerdebug("SELECT * from ".$this->table.' where '.$where.$orderBy);
			$statement = bddModel::getConnect($this->connection)->prepare("SELECT * from ".$this->table.' where '.$where.$orderBy.$limit);
			$this->execute($statement);
        	$list = $statement->fetchAll(PDO::FETCH_BOTH);        	
        	return $list;
        
		}catch(PDOException $e){
			Logger::tracerErreur("Erreur connex");
			return false;
		}	
	}
	private function returnWhere($conditions = array()){
		if($conditions == array()) return "1";
		$chaine = "";
		foreach($conditions as $key => $val){
			$chaine = $chaine.' AND '.$key.' = "'.$val.'"';
		}
		$chaine = substr($chaine,4);
		return($chaine);
	}

	private function returnOrder($order = array()){
		if($order == array()) return $chaine = "";
		$chaine = '';
		foreach($order as $key => $val){
			$chaine = $chaine.",".$key." ".$val;
		}
		$chaine = "order by ".substr($chaine,1);
		return($chaine);
	}
	private function returnSet($value = array()){
		$set = '';
		foreach ($value as $clef => $valeur){
			$set .= $clef.'="'.$valeur.'"'.',';
		}
		$set = substr($set,0,-1);
		return($set);
	}
	public function add($hashMap_toAdd){
		$elem = '';
		$val = '';
		foreach($hashMap_toAdd as $clef => $valeur){
			if($valeur){
				$valeur = addslashes ($valeur);
				$elem = $elem.", ".$clef;
				$val .= "'$valeur',";
			}
		}
		$elem = substr($elem, 1);
		$val = substr($val,0,-1);
		$sql = "insert into ".$this->table."($elem) values($val)";
		Logger::tracerdebug($sql);
		$statement = bddModel::getConnect($this->connection)->prepare($sql);
		return $this->execute($statement);
	}
// 	public function getLastInseredID(){
// 		return $this->query('SELECT LAST_INSERT_ID();');//Semble ne pas fonctionner
// 	}
	public function delete($hashMap_toRemove){
		$where = $this->returnWhere($hashMap_toRemove);
		$statement = bddModel::getConnect($this->connection)->prepare("delete from $this->table where $where");
		return $this->execute($statement);
	}
	public function update($hashMap_toUpdate,$conditions = array()){
		$where = $this->returnWhere($conditions);
		$set = $this->returnSet($hashMap_toUpdate);
		$statement = bddModel::getConnect($this->connection)->prepare('update '.$this->table.' set '.$set.' where  '.$where.'');
		return $this->execute($statement);
	}
	public function query($sql){	       	
		try{
			Logger::tracerdebug($sql);
			$statement = bddModel::getConnect($this->connection)->prepare($sql);
			$this->execute($statement);		       	
        	$list = $statement->fetchAll(PDO::FETCH_ASSOC);        	
        	return $list;
		}catch(PDOException $e){
			Logger::tracerErreur("Erreur connex");
			return false;
		}
	}
	
	private function execute($statement){
		 //echo '<pre>'.print_r($statement).'</pre>';
		if($statement->execute()){
			return true;
		}
		
	}
	public function getLastId()
	{
		return bddModel::getConnect($this->connection)->lastInsertId();
	}
		//Static
	private static $pdoConnect;
	private static $connections;
	private static $db_config = NULL;
	private static function getConnect($name){	
	
		
		if(bddModel::$db_config == NULL){		
			require_once "config/database.php";
			$data = new db_config();			
			bddModel::$db_config = $data;
			$database = $data->$name;
			
		}else{
			$database = bddModel::$db_config->$name;
		}
		try{
			$connect = new PDO($database["sgbd"].":
                            host=".$database["host"].";
                            dbname=".$database["bdd"],
                            $database["login"] ,
                            $database["pass"]);
			$connect->query("SET NAMES 'utf8'");
			bddModel::$connections = $connect;
		}
		catch(PDOException $e){
			Logger::tracerErreur("Erreur connex");
                   // throw new DataBaseException("Connexion a la base echou&eacute; !");
                    exit();
		}
		
		return $connect;
	}
	
}


?>
