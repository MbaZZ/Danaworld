<?php
require_once 'controller/layoutManagerCache.php';
class disptacher implements dispatcherInterface{
	private $configXml;
	private $layoutObj;
	private $interceptors_loaded = array(); //Liste de tous les intercepteurs déja chargés
	private $current_content_name = "content_url";
	private $global_interceptors = array();//Tableau setter dans router et merger avec les autres

	function __construct($configXml){
		$this->configXml = $configXml;
		if($this->configXml->jsAndCssCache == "Activer")
			$this->layoutObj = new layoutManagerCache($this->configXml->layoutName);
		else
			$this->layoutObj = new layoutManager($this->configXml->layoutName);
		
		if(isset($configXml->globalInterceptors))
			foreach($configXml->globalInterceptors->children() as $it_name){
			$attributs = $it_name->attributes();
			if(isset($attributs['class']))
				$this->global_interceptors[] = $attributs['class']."";
		}
	}
	/**
	 * Créé une instance d'un contrôleur, appelle son action, récupère la vue et la fournit au layout_manager
	 * charge les éventuels intercepteurs
	 * @param controller_name nom du contrôleur
	 */
	public function loadController($controller_name, $action_name, $content_name = null ,$param = array()){
		$controller_class_name = $controller_name.'_controller';
		try{
			if($content_name != null )
				$this->content_name = $content_name;
			else
				$content_name = $this->content_name;
			$this->layoutObj->changeNameSpace($controller_name);
			//TODO APOR Faire mieux pour getCurrentActionName
			if($action_name == 'getCurrentActionName' || !@include_once('controller/'.$controller_name.'_controller.php')){
				throw ControllerException::ControllerNotFoundException ($controller_name);
				return;
			}			
			$view_to_add = NULL;
			$view_name = $this->loadControllernGetView($controller_class_name, $action_name,$param);
			$this->layoutObj->addContent($content_name, $view_name, $controller_name);
			return $view_name;
		}catch(Exception $e){
			Logger::tracerException($e);
			//$this->layoutObj->addContent($content_name, "messages/indisponible.html");
			Logger::tracerDebutInterception("indisponible_interceptor::init", $controller_class_name);
			$itObj = $this->initInterceptor('indisponible');
			Logger::tracerFinInterception("indisponible_interceptor", $controller_class_name);			
			$view_filePath = $itObj->beforeController($this->layoutObj);
			if($view_filePath != NULL){//Si l'intercepteur demande une vue, alors on court-circuite la suite
				$this->layoutObj->addContent($content_name, $view_filePath, $controller_name);
				return $view_filePath; //On sort de la fonction
			}
			Logger::tracerDebutInterception("indisponible_interceptor::afterController", $controller_class_name);
			$view_filePath = $itObj->afterController($this->layoutObj);
			Logger::tracerFinInterception("indisponible_interceptor::afterController", $controller_class_name, $view_filePath);
			$this->layoutObj->addContent($content_name, $view_filePath, $controller_name);
			return $view_filePath;
		}
	}

	private function loadControllernGetView($controller_class_name, $action_name, $param = array()){
		Logger::tracerDebutController($controller_class_name);
		//Avant de charger le controlleur, on regarde ses intercepteurs
		$tmp_list_interceptors = array();
		$view_filePath = NULL;

		if($controller_class_name != 'fichiers_controller'){
			$interceptors = array_merge($this->global_interceptors, $controller_class_name::getInterceptors());
			$this->global_interceptors = array();//On ne les charge qu'une fois
			foreach($interceptors as $interceptor_name){
				Logger::tracerDebutInterception($interceptor_name."_interceptor::init", $controller_class_name);
				$itObj = $this->initInterceptor($interceptor_name);
				Logger::tracerFinInterception($interceptor_name."_interceptor::init", $controller_class_name);
				if($itObj != false){
					$tmp_list_interceptors[] = $itObj;
					Logger::tracerDebutInterception($interceptor_name."_interceptor::beforeController", $controller_class_name);
					$view_filePath = $itObj->beforeController($this->layoutObj);
					Logger::tracerFinInterception($interceptor_name."_interceptor::beforeController", $controller_class_name, $view_filePath);
					if($view_filePath != NULL){//Si l'intercepteur demande une vue, alors on court-circuite la suite
						return $view_filePath; //On sort de la fonction
					}
				}
			}
		}
		//Ensuite le controleur
		Logger::tracerDebutFonction($controller_class_name."::init");		
		$controllerObj = new $controller_class_name($this->layoutObj); //Chargement init
		Logger::tracerFinFonction($controller_class_name."::init");
		
		Logger::tracerDebutAction($controller_class_name."::".$action_name);
		$view_filePath = $controllerObj->loadAction($action_name,$param); //Chargement action
		Logger::tracerFinAction($controller_class_name."::".$action_name, $view_filePath);
		if($controller_class_name != 'fichiers_controller'){//Sauf cas particulier fichier
			//Enfin, chargement des intercepteur d'apres controleur
			foreach($tmp_list_interceptors as $interceptorObj){
				Logger::tracerDebutInterception($interceptor_name."_interceptor::afterController", $controller_class_name);
				$after_view_filePath = $interceptorObj->afterController($this->layoutObj);
				Logger::tracerFinInterception($interceptor_name."_interceptor::afterController", $controller_class_name, $view_filePath);
				if($after_view_filePath != NULL)
					return $after_view_filePath;
			}
		}
		Logger::tracerFinController($controller_class_name);
		return $view_filePath; //Si aucun court-circuit alors on prend la vue du controlêur
	}
	function getLayoutObj(){
		return $this->layoutObj;
	}
	/*
	 * Charge la première partie de l'intercepteur, une seul fois par requete
	*/
	private function initInterceptor($name){
		if(!isset($this->interceptors_loaded[$name])){ //Si pas deja chargé
			$class_name = $name."_interceptor";
			require_once "interceptor/".$class_name.".php";
			$this->interceptors_loaded[$name] = true; //TODO Enlever warning !
			return new $class_name($this->layoutObj);
		}
		return false; //Intercepteur déja chargé ailleur
	}

}
