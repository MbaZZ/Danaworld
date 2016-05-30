<?php
class appModel implements ModelInterface{
	protected $data = array();
	//all data
	function setData($data = array()){
		$this->data = $data;
	}
	function getData(){
		return $this->data;
	}

	//get only attr
	function __set($var_name, $value){
		Logger::tracerdebug('Il y a un set de '.$var_name.' la avec du '.$value);
		$this->data[$var_name] = $value;
	}
	function __get($var_name){
		if(isset($this->data[$var_name])){
			return $this->data[$var_name];
		}else{
			return null;
		}
	}
	public function isValid(){
		return true;
	}
	
	/**
	* Méthode de débug
	* Renvoi en string le contenu des attributs
	* @arg String Affichage des attributs
	*/
	public function __toString(){
		$res = "<table>";
		foreach($this->data as $name => $val){
			$res .= "<tr><td>".$name."</td><td>".$val."</td></tr>";
		}
		return $res."</table>";
	}
}
?>