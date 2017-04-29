<?php
require_once "exception/ViewControllerException.php";
class layoutManager implements Layout_managerInterface{	
	private $layout;
	private $header = array();
	private $title = '';
	private $view;
	
	/*
	 * Version actuel : On garde les 2 systèmes global et par namespace
	 */
	private $zone = array();
	
	//Ancien fonctionnement
	private $vars = array();
	private $helpers = array();
	
	//Nouveau fonctionnement
	private $zoneToNameSpace = array(); //permet de retrouver le nom du namespace depuis le nom de la zone
	private $nameSpaces = array();// les variables et helpers sont stoqués par namespace afin de permettre le multi controller
	private $currentNameSpaceName = "global";
	private $currentNameSpace = NULL; //entrée courrante de $nameSpaces
	
	function __construct($layoutName){
		$this->layout = $layoutName;
	}
	//chagement de namespaces

	function changeNameSpace($controller_name){
		$this->currentNameSpaceName = $controller_name;
		if(!isset($this->nameSpaces[$controller_name])){
			Logger::tracerInfo("Le namespace ".$controller_name." n'éxiste pas, création !");
			$this->nameSpaces[$controller_name] = array('vars', 'helpers');
		}
		$this->currentNameSpace = &$this->nameSpaces[$controller_name];
	}	

	//getVars
	function __set($var_name, $value){
		$this->currentNameSpace['vars'][$var_name] = $value; //Version namespace
		$this->vars[$var_name] = $value; //assure la non reg
	}
	function __get($var_name){
		if(!isset($this->currentNameSpace['vars'][$var_name])){ //cas nouveau système bug (a enlever une fois éprouvé)
			Logger::tracerdebug('La variable '.$var_name.' n\'éxiste pas dans le namespace '.$this->currentNameSpaceName.' récupération dans les variables global...');
			if(isset($this->vars[$var_name]))
				return $this->vars[$var_name]; //on remet l'ancien système
			else
				Logger::tracerErreur('La variable '.$var_name.' appellée depuis la vue n\'existe pas !');
		}
		return $this->currentNameSpace['vars'][$var_name];
	}
	/**
	 * Retourne toutes les variables de tous les namespace
	 * @return array
	 */
	function getVars(){
		return $this->vars;
	}

	//getHelper
	function addHelper($helperName, $helperObj){
		$this->currentNameSpace['helpers'][$helperName] = $helperObj;//Version namespace
		$this->helpers[$helperName] = $helperObj; //assure la non reg
	}
	function getHelper($helperName){
		if(!isset($this->currentNameSpace['helpers'][$helperName])){ //cas nouveau système bug (a enlever une fois éprouvé)
			Logger::tracerdebug('Le helper '.$helperName.' n\'éxiste pas dans le namespace '.$this->currentNameSpaceName.' recupération dans le namespace global');
			if(!isset($this->helpers[$helperName])){
				Logger::tracerErreur('Le helper '.$helperName.' n\'éxiste pas ! creation depuis la vue..');
				require_once 'view/helper/'.$helperName.'.php';
				$helperObj = new $helperName($this);
				$this->addHelper($helperName, $helperObj);
			}
				
			return $this->helpers[$helperName];
		}
		return $this->currentNameSpace['helpers'][$helperName];
	}

	//nameLayout

	function setLayoutName($layoutName){
		Logger::tracerdebug("setLayoutName".$layoutName);
		$this->layout = $layoutName;
	}
	function setView($view){
		$this->view = $view;
	}

	//load
	function addContent($name,$viewPath, $controllerName = "global"){
		Logger::tracerInfo('addContent '.$controllerName);
		$this->zoneToNameSpace[$name] = $controllerName;
		$this->zone[$name] = $viewPath;
	}
	function getContent($name){
		$this->changeNameSpace($this->zoneToNameSpace[$name]);
		$zoneName = $this->zone[$name];
		Logger::tracerDebutFonction("layoutManager::getContent","content_name : ".$name );
		if(is_array($zoneName)){
			Logger::tracerdebug("debug 1");
			foreach($zoneName as $k => $v){
				Logger::tracerdebug(".^:/. " .$k.' ->'.$v);
			}
		}
		try{
			$DebugView = "";
			if($zoneName == "") //Pas de vue
				Logger::tracerInfo("Pas de contenu pour la zone ".$name);
			else if($this->view){
				$DebugView = $this->view;
				Logger::tracerDebutFonction("include ".$DebugView);
					include "view/".$this->view;
				Logger::tracerFinFonction("include ".$DebugView);
			}else if(is_string($zoneName)){ //Une seul vue
				$DebugView = $zoneName;
				Logger::tracerDebutFonction("include ".$DebugView);
					include "view/".$zoneName;
				Logger::tracerFinFonction("include ".$DebugView);
			}else if(is_array($zoneName)){ //Plusieurs vues
				foreach($zoneName as $view){
					$DebugView .=" ".$view;
					Logger::tracerDebutFonction("include ".$DebugView);
						include "view/".$view;
					Logger::tracerFinFonction("include ".$DebugView);
				}			
			}
		}catch(Exception $e){
			Logger::tracerException($e);
			include "view/messages/indisponiblehtml";
		}
		Logger::tracerFinFonction("layoutManager::getContent");		
		
	}
	function render(){
		Logger::tracerDebutFonction("layoutManager::render","layout : ".$this->layout);
		include "view/layout/".$this->layout;
		Logger::tracerFinFonction("layoutManager::render");
	}

	/*
	 * @param $content_name NULL si cache global
	*/
	function saveCache($content_name = NULL){
	}
	function loadCache($content_name = NULL){
	}
	function deleteCache($content_name = NULL){
	}

	//utils
	function getHeaders(){
		//return $this->header;
		return implode(array_unique($this->header));
	}
	function addHeader($headerTxt, $after = true){
		//$this->header = $after ? $this->header.$headerTxt : $headerTxt.$this->header;
		if(!$after)
			array_unshift ($this->header, $headerTxt);
		else
			array_push ($this->header, $headerTxt);

	}
	/**
	 * Enleve les headers passé en paramètre
	 * @param array headersArray = array("fichier1.css", "fichier2.js", ...)
	 */
	function removeHeaders($headersArray){
		foreach($this->header as $h){
			foreach($headersArray as $hr){
				if(strpos($h, $hr) != false){
					unset($h);
					unset($hr);
				}
			}
		}
	}
	function addCss($file, $after = true){
		$path = ROOT."files/css/".$file.".css";
		if(!file_exists("files/css/".$file.".css")){
			$path = LIB."files/css/".$file.".css";
		}
		$this->addHeader("<link href='$path' title='design' type='text/css' media='all' rel='stylesheet' />",$after);
	}

	function addJs($file, $after = true){
		$filePath = "files/js/".$file.".js";
		$headerPath = ROOT.$filePath;
		if(!file_exists($filePath)){
			$headerPath = LIB.$filePath;
			//Gestion des fichiers virtuels avec fichiersController si le fichier reel n'existe pas
			if(!file_exists(router::getFramworkPath().$filePath)){ 
				$file = explode('?', $file); $file = $file[0];
				$headerPath = ROOT."fichiers/js/".$file.".js";
			}
		}
		$this->addHeader("<script src='$headerPath' type='text/javascript'></script>",$after);
	}
	function setTitle($title){
		if($this->title == '') //Ne garde que le premier titre définit (contrôleur le plus haut)
			$this->title = $title;
	}
	function getTitle(){
		return $this->title;
	}
}
?>