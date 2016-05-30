<?php
class appController extends controller{
	
	protected $helpers = array();
	protected $models = array();
	private $helpernmodel = array();
	public static $interceptors = array();
	
	protected $layoutObj;	
	
// 	public function __set($name,$value){
// 		$this->helpernmodel[$name] = $value;
// 	}
	
	public function __get($name){
		Logger::tracerInfo('get  '.$name);
		return $this->helpernmodel[$name];
	}
	
	public function appController($layout){
// 		parent::__construct($layout);

		foreach ($this->helpers as $helper)
		{
			require_once 'view/helper/'.$helper.'.php';
			$helperObj = new $helper($layout);
			$layout->addHelper($helper, $helperObj);
			$temp = $helper.'Helper';
			Logger::tracerInfo('ajout  '.$temp);
// 			$this->$temp = $helperObj;
			$this->helpernmodel[$temp] = $helperObj;
		}
		
		foreach ($this->models as $model)
		{
			require_once "model/".$model.".php";
			$modelObj = new $model();
			$temp = $model.'Model';
			
// 			$this->$temp = $modelObj;
			$this->helpernmodel[$temp] = $modelObj;
		}
		$this->layoutObj = $layout;
		$this->init($layout);
	}

	public function loadAction($action_name,$param = null){
		parent::loadAction($action_name);
		$this->$action_name(isset($param['id'])?$param['id']:array());
		return substr(get_class($this),0,-11).'/'.$action_name.'.html';		
	}
	public function debug($var = array()){
		echo '<pre>';
		print_r($var);
		echo '</pre>';
	}
	public function getConfig(){
		return  simplexml_load_file("config/project.xml");
	}
}
?>