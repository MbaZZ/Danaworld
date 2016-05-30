<?php
	class fileModel extends appModel implements daoInterface{
	protected $filePath = "data/file.data";
	private $maps = array();
	
	public function get($id){
		$this->loadData();
		if(isset($this->maps[$id]))
			return $this->maps[$id];
		else return NULL;
	}
	public function getList($hashMap_conditions = "", $options = array()){
		$this->loadData();
		return $this->maps;
	}
	public function add($hashMap_toAdd){
		$this->loadData();
		$this->maps[$hashMap_toAdd['id']] = $hashMap_toAdd;		
		$this->saveList();
	}
	public function saveList(){
		//Sauvgarde dans le fichier
		$handle = fopen($this->filePath, "c+");
		fwrite($handle, serialize($this->maps));
		fclose($handle);
	}
	public function delete($hashMap_toRemove){
		$this->loadData();		
		unset($this->maps[$hashMap_toRemove['id']]);
		$this->saveList();	
	}
	public function update($hashMap_toUpdate){
		$this->loadData();		
		$this->maps[$hashMap_toRemove->id] = $hashMap_toUpdate;
		$this->saveList();
	}

	/**
	* Charge le fichier
	* Récupère les variables
	* dans le fichier de sav
	*/
	private function loadData(){
		$this->maps = array();
		$handle = fopen($this->filePath, "c+");		
		$file_len = filesize($this->filePath);
		if($file_len > 0 ){// Inutil de lire le fichier si vide 
			$this->maps = unserialize(
				fread($handle, $file_len)
			);		
		}
		fclose($handle);
	}
	/**
	* Supprime le fichier
	* càd toutes les variables liées
	*/
	public function erazeAll(){
		$desc = fopen ($this->filePath, 'w');
	 	fclose($desc);
	}
}
?>
