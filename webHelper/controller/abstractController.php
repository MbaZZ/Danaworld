<?php
class abstractController extends Controller{
	protected $layoutObj;
	private $modelsnhelpers = array();
	private $prefix_view_path = "";
	public static $interceptors = array();
	
	protected function setPrefixViewPath($path){
		$this->prefix_view_path = $path;
	}
	
	public function abstractController($layout){
		parent::__construct($layout);
		//helper par default : HTML
		$this->addHelper("html");
	}
	public function loadAction($action_name, $param = null){
		parent::loadAction($action_name);
		/*if(!isset($this->$action_name))
		   throw ControllerException::ActionNotFoundException ($action_name);
		*/
		
		$returned =  $this->$action_name($this->layoutObj,$param);	
		if($returned == "noView") return "";
		
		if(!is_array($returned))
			$returned = $this->prefix_view_path.$returned;
		if($returned != null && $returned != false)
			return $returned;
		else
			return substr(get_class($this),0,-11).'/'.$action_name.'.html';	
		
	} 
	protected function addHelper($name){
		$varName = $name."Helper";
		if(isset($this->$varName)) return;
		require_once 'view/helper/'.$name.'.php';		
		
		$this->$varName = new $name($this->layoutObj);
		$this->layoutObj->addHelper($name, $this->$varName);
	}
	protected function addModel($name){
		$varName = $name."Model";
		if(!isset($this->$varName)){
		require_once "model/".$name.".php";				
		$this->$varName = new $name();
		}
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
	public function __call($method, $arg){
		if(get_class($arg[0]) == "layoutManager" || is_subclass_of($arg[0], "layoutManager"))
			return $this->actionNotFound($arg[0], $method, $arg[1]);
		else
		   throw ControllerException::MethodeNotFoundException($method);
		return NULL;
	}
	public function actionNotFound($html, $methodName, $params = NULL){
		throw ControllerException::ActionNotFoundException(ucfirst($methodName));
	}
}
?>