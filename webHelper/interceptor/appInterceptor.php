<?php
/*
* Un intercepteur est un outils de factorisation de code, il peut �tre ex�cuter avant un ou plusieurs controlleurs
*/

class appInterceptor implements interceptorInterface{
	private $configController;
	private $layoutObj;
	private $modelsnhelpers = array();
	
	public function __construct($layout){
		$this->layoutObj = $layout;
		$this->init($layout);		
	}
	protected function init($layout){
	
	}
	public function beforeController($layout){
		return NULL;
	}
	public function afterController($layout){
		return NULL;
	}
	protected function addHelper($name){
		require_once 'view/helper/'.$name.'.php';
		$varName = $name."Helper";
		
		$this->$varName = new $name($this->layoutObj);
		$this->layoutObj->addHelper($name, $this->$varName);
	}
	protected function addModel($name){
		require_once "model/".$name.".php";
		$varName = $name."Model";
		
		$this->$varName = new $name();
	}
	protected function loadControllerAction($controller_name, $action_name){
		return router::getInstance()->addController($controller_name, $action_name);
	}
	public function __get($modeluHelper_name){
		if(isset($this->modelsnhelpers[$modeluHelper_name]))
			return $this->modelsnhelpers[$modeluHelper_name];
		else{
			echo "<p>Element ".$modeluHelper_name." Introuvable !!</p>";
			return NULL;	
		}
	}
	public function __set($modeluHelper_name, $obj){
		$this->modelsnhelpers[$modeluHelper_name] = $obj;
	}	
}
?>