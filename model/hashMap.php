<?php
/**
* Cette classe remplace les tableaux pour les données
*/
class hashMap implements ArrayAccess{
	private $attributs = array();
	public function hashMap($datas = NULL){	
		if($datas != NULL)
			$this->setAttributs($datas);
	}
	/**
	* Get & Set
	* Permet de définit d'un coup tous les attributs
	* @arg array|hashMap $attr Un tableau d'attributs
	*/
	public function setAttributs($datas){	
		if(is_array($datas)) 
			$this->attributs = $datas;
		else if(get_class($datas) == "hashMap")
			$this->attributs = $this->attributs;			
	}
	public function toArray(){
		return $this->attributs;
	}
	/**
	* Obtenir un attribut
	*/
	//Methode ->
	public function __set($attribut_name, $value){
		$this->attributs[$attribut_name] = $value;
	}
	public function __get($attribut_name){
		return $this->attributs[$attribut_name];
	}
	//Methode []
	public function offsetExists($attribut_name){ 
		return isset($this->attributs[$attribut_name]);
	}
	public function offsetGet($attribut_name){
		return $this->attributs[$attribut_name];
	}
	public function offsetSet($attribut_name, $new_valeur){
		$this->attributs[$attribut_name] = $new_valeur;
		return $this->attributs[$attribut_name];
	}
	public function offsetUnset($attribut_name){
        unset($this->attributs[$attribut_name]);
	}
	
	/**
	* Méthode de débug
	* Renvoi en string le contenu des attributs
	* @arg String Affichage des attributs
	*/
	public function __toString(){	
		
		$res = "<table>";
		foreach($this->attributs as $name => $val){
			$res = $res."<tr><td>".$name."</td><td>".$val."</td></tr>";
		}
		$res = $res."</table>";
	
		return $res;
	}
	public function toJson(){
		return $this->attributs;
	}
}

class hashMapList implements iterator, ArrayAccess{
	protected $index = 0;
	protected $maps = array();
	
	public function hashMapList($maps = array()){
		$this->setMaps($maps); 
	}
	public function setMaps($maps){
		if(is_array($maps))
			$this->maps = $maps;
	}	
	public function size(){
		return sizeof($this->maps);
	}
	public function sort($sort_flags = SORT_REGULAR){
		sort($this->maps);
	}
	/**
	* Rajotue un hashMap
	*/
	//avec son id
	public function add($hashMap_toAdd){
		$this->maps[$hashMap_toAdd->id] = $hashMap_toAdd;
	}
	//en debut
	public function enfiler($hashMap_toAdd){
		array_unshift($this->maps, $hashMap_toAdd);
	}
	//en fin
	public function empiler($hashMap_toAdd){
		array_push($this->maps, $hashMap_toAdd);
	}	
	public function toArray(){
		return $this->maps;
	}
	public function current(){  
		return $this->maps[$this->index];
	}
	public function key(){
		return $this->index;
	}
	public function next(){
		$this->index+=1;
	}
	public function valid(){
		return $this->index < count($this->maps);
	}	
	public function rewind(){
		$this->index = 0;
	}
	/*
	* Impl�mentation de arrayAccess
	*/
	public function offsetExists($index){ 
		return isset($this->maps[$index]);
	}
	public function offsetGet($id){
		return $this->maps[$id];
	}
	public function offsetSet($id, $newHashMap){
		$this->maps[$id] = $newHashMap;
		return $newModel;
	}
	public function offsetUnset($id){
		unset($this->maps[$id]);
	}
	public function __toString(){
		$res = "<table>";		
		foreach($this->maps as $map){
			$res .= "<tr><td>".$map."</td></tr>";
		}
		$res .="</table>
		<p>".count($this->maps)." Enregistrement(s)</p>";
		
		return $res;
	}
	public function toJson(){
		$ret = array();
		foreach($this->maps as $m){
			if(gettype($m) == 'object' && (get_class($m) == 'hashMap' || get_class($m) == 'hashMapList'))
				$ret[] = $m->toJson();
			else
				$ret[] = $m;
		}
		return $ret;
	}
	
}
?>
