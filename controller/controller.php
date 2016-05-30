<?php
class controller implements controllerInterface{
	protected $layoutObj;
	protected static $interceptors = array();
	private $currentAction;
	static function getInterceptors(){
		$child_controller_name = get_called_class();
		return $child_controller_name::$interceptors;
	}

	function __construct($layoutObj){
		$this->layoutObj = $layoutObj;
		$this->init($layoutObj);
	}
	public function loadAction($actionName){
		$this->currentAction = $actionName;
	}
	public function init($layoutObj){
	}
	public function getCurrentActionName(){
		return $this->currentAction;
	}
	//Utils
	public function isAjaxRequest(){
		return isset($_POST['request']) && $_POST['request'] == "ajax" || isset($_GET['request']) && $_GET['request'] == "ajax";
	}
}
?>