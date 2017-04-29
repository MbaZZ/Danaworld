<?php


require_once "interfaces.php";

class router implements routerInterface{

	// STATIC MOD
	private static $factory = NULL;
	private static $instanceID = NULL;

	public static function getInstance(){
		if(router::$factory == NULL)
			return new router();   //L'affectation est dans le constructeur
		else
			return router::$factory;
	}
	static function getRequestID(){
		if(router::$instanceID==NULL) router::$instanceID = 'i'.rand();
		return router::$instanceID;
	}
	
	private static $framwork_path = "../danaworld/";
	private static $addedincludePath = "";
	public static function setFramworkPath($path){
		
		
		router::$framwork_path = $path;
	}
	public static function addIncludePath($path){
		router::$addedincludePath .= PATH_SEPARATOR.router.$path;
	}
	public static function getFramworkPath(){
		return router::$framwork_path;
	}
	
	// Instance
	private $config_projectXML;
	private $disptacherObj;
	public function router(){		
		date_default_timezone_set('Europe/Paris'); 
		router::$factory = $this; //Pour pr�cisser qu'une instance de router existe d�ja
		if($_SERVER['SCRIPT_NAME'] <> '/index.php' ){
			$root = dirname($_SERVER['SCRIPT_NAME']).'/';
		}
		else{
			$root = '/';
		}
	
		if($root == "/"){
			$lib = '/danaworld/';
		}else{
			//Gestion des site ayant un path relative avec danaworld diff�rent
			//On compte le nombre de ../ et on met les dirname en cons�quent
			$nb = substr_count(router::$framwork_path, '../');
			$result = $_SERVER['SCRIPT_NAME'];
			for($i=0;$i<=$nb;$i++){	
				$test = dirname($result);
				if($test <> '/'){
					$result = dirname($result);
				}
				else{
					$result = '';
				}
			}		
			$lib =  $result."/danaworld/";
		}
		define('ROOT', $root);
		define('LIB',$lib);
		define('IS_AJAX_REQUEST', isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&	strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
		//set_include_path("./".PATH_SEPARATOR.router::$framwork_path);
		set_include_path('./'.PATH_SEPARATOR.get_include_path().PATH_SEPARATOR.router::$framwork_path.router::$addedincludePath);

		//Exceptions
		require_once "exception/LibraryNotFoundException.php"; //messages
		require_once "exception/ControllerException.php";
		
		//xmlconfig
		if(!($this->config_projectXML = simplexml_load_file("config/project.xml")))
			throw LibraryNotFoundException::xmlConfigNotFound ();
			
		if(isset($this->config_projectXML->projectMod)){
			$projectMod = $this->config_projectXML->projectMod;
			define('PROJECT_MOD', $projectMod = $this->config_projectXML->projectMod);
			if($projectMod == "dev" || $projectMod == "preprod"){
				ini_set('display_errors', '1');
			}
			else{
				ini_set('display_errors', '0');
			}
		}
		
		//Gestionnaire des traces
		require_once "utils/Logger.php";
		//TODO selection du niveau de trace
		Logger::tracerDebutFonction(__FUNCTION__);
		//$errorHandler = set_error_handler("errorHandler");	
		
		//Import controllers
		require_once "controller/controller.php";
		require_once "controller/appController.php";
		require_once "controller/abstractController.php";

		//Import interceptor
		require_once "interceptor/appInterceptor.php";
			
		//Layout
		require_once "controller/layoutManager.php";

		require_once "controller/dispatcher.php";
		$this->disptacherObj =  new disptacher($this->config_projectXML);
		//Import Model
		require_once "model/appModel.php";
		require_once "model/hashMap.php";

		//helper
		require_once "view/helper/appHelper.php";
		try{
			if(isset($_GET['url'])){
				$url = explode("/",$_GET['url']);				
			}else{
				$url = explode("/", $this->config_projectXML->defaultUrl);
			}
			$controller = $url[0];
			$action = isset($url[1]) ? $url[1] : 'home';
			//$id = isset($url[2]) ? $url[2] : null;
			$id = isset($url[2]) ? implode('/',array_slice($url, 2)) : null;
			
			//Securisation des entrants : recherche de l'entré dans le project.xml			
			$trouve=false;
			if($this->config_projectXML->ressourcesDisponibles){
				foreach($this->config_projectXML->ressourcesDisponibles->children() as $elem){
					$attr = $elem->attributes();
					if(isset($attr['controller']) && $attr['controller'] == $controller 
						&& isset($attr['action']) && $attr['action'] == $action
						&& (
							IS_AJAX_REQUEST && isset($attr['ajax']) && $attr['ajax'] == 'true'
							|| !IS_AJAX_REQUEST && isset($attr['synchrone']) && $attr['synchrone'] == 'true'
							)
				){
						$trouve=true;
						break;
					}
				}	
				if(PROJECT_MOD == 'dev'){
					if(!$trouve){
						$elem = $this->config_projectXML->ressourcesDisponibles->addChild('request');
						$elem->addAttribute('controller', $controller);
						$elem->addAttribute('action', $action);
					}
					$attr = $elem->attributes();
					if(IS_AJAX_REQUEST){
						if(!isset($attr['ajax'])) $elem->addAttribute('ajax', 'true');
					}else{
						if(!isset($attr['synchrone']))$elem->addAttribute('synchrone', 'true');
					}
					$this->config_projectXML->asXML("config/project.xml");
				}else{
					if(!$trouve){
						throw new ControllerException("Action interdite ".$controller.' '.$action . ' ajax ' .IS_AJAX_REQUEST);
						return;
					} 
				}
			}
			$this->getDispatcher()->loadController($controller,$action,'content_url',$param = array('id'=>$id));
			Logger::tracerFinFonction(__FUNCTION__);
		}catch(Exception $e){
			Logger::tracerException($e);
			$this->getDispatcher()->loadController('indisponible','home','content_url');
		}
	}
	public function addController($controller_name, $action_name, $content_name = null,$param = array()){
		return $this->getDispatcher()->loadController($controller_name, $action_name, $content_name ,$param);
	}
	public function getDispatcher(){
		return $this->disptacherObj;

	}
	public function getConfig(){
		return $this->config_projectXML;
	}

	public function run(){
		$this->getDispatcher()->getLayoutObj()->render();
	}
}


/** Permet de canaliser les erreurs */
function errorHandler($errno, $errstr, $errfile, $errline)
{
	if (!(error_reporting() & $errno)) {
		// Ce code d'erreur n'est pas inclus dans error_reporting()
		return;
	}

	switch ($errno) {
		case E_USER_ERROR:
			Logger::tracerErreur("<b>Mon ERREUR</b> [$errno] $errstr\n");
			Logger::tracerErreur( "  Erreur fatale sur la ligne $errline dans le fichier $errfile");
			Logger::tracerErreur( ", PHP " . PHP_VERSION . " (" . PHP_OS . ")\n");
			Logger::tracerErreur( "Arr�t...\n");
			break;

		case E_USER_WARNING:
			Logger::tracerWarning( "[$errno] $errstr ($errfile:$errline)\n");
			break;

		case E_USER_NOTICE:
			Logger::tracerWarning( "[$errno] $errstr ($errfile:$errline)\n");
			break;
		default:
			Logger::tracerWarning( "Type d'erreur inconnu : [$errno] $errstr ($errfile:$errline)\n");
			break;
	}

	/* Ne pas ex�cuter le gestionnaire interne de PHP */
	$configXml = router::getInstance()->getConfig();
	if($configXml == NULL || $configXml->projectMod == "" || $configXml->projectMod != "dev"){
		return true;
	}else{
		return false;
	}
}
?>
