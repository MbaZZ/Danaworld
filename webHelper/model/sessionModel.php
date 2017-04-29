<?php
class sessionModel extends appModel{
	private $sessionName = 'sessionModel.undefined';
	protected $data = array();
	
	public function sessionModel(){
		$this->sessionName = 'sessionModel.'.get_class($this);
		$this->loadData();
		if(!isset($_SESSION[$this->sessionName])){
			$_SESSION[$this->sessionName]=array();
			Logger::tracerErreur('Reinit session');
		}
	}
	
	/*
	 * Persistence
	*/
	public function getParam($sName){
		if(!isset($_SESSION['brique'][$sName])){
			$_SESSION[$this->sessionName][$sName] = null;
		}
		return $_SESSION[$this->sessionName][$sName];
	}
	public function setParam($sName, $value){
		$_SESSION[$this->sessionName][$sName] = $value;
		return $_SESSION[$this->sessionName][$sName];
	}
	function __set($var_name, $value){
		parent::__set($var_name, $value);
		$this->setParam($var_name, $value);
	}
	function __get($var_name){
		return $this->getParam($var_name);
	}
	protected function loadData(){
		if(!isset($_SESSION[$this->sessionName])){
			Logger::tracerErreur('Session vide !');
		}else{
			$this->data = $_SESSION[$this->sessionName];
		}
	}
	function getData(){
		$this->loadData();
		return $this->data;
	}
	protected function saveData(){
// 		echo "Save !"; print_r($this->data);
		if(!isset($_SESSION[$this->sessionName])){
			Logger::tracerErreur('Reinit session');
		}else{
			$_SESSION[$this->sessionName] = $this->data;
		}
	}
}
?>
