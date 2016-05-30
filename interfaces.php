<?php
// --- Router ---
/**
* Cette classe est piloter par un fichier de configuration XML,
* Ce fichier doit être dans projet/config/projet.xml
*/
interface routerInterface{
	static function getInstance();
	function addController($controller_name, $action_name, $content_name ,$param = array());
	function getDispatcher();
	function getConfig();
}

// --- Dispatcher ---
/**
* Creer l'instance de layout_manger
*/
interface dispatcherInterface{

	function __construct($config_dispatcherXML);
	/**
	* Créer un instance d'un controlleur, appelle son action, récupère la vue et la fourni au layout_manager
	* charge les éventuelles intercepteurs
	* @param controller_name nom du contrôleur
	*/	
	function loadController($controller_name, $action_name, $content_name ,$param = array());

	/**
	* Lors de la destruction, si aucune page chargé, et que page par default définit alors affiche page default sinon Exception (unused)
	*/
// 	function __destroy();
	
}


// --- Controller ---
interface controllerInterface{	
	function __construct($layoutObj);
	function loadAction($actionName);
	function init($layoutObj);
	static function getInterceptors(); //Récupère la liste des intercepteurs demander par le controlleur
}

// --- Interceptors ---
interface interceptorInterface{
	function __construct($layoutObj);
	function beforeController($layout);
	function afterController($layout);
}

// --- Element ---
interface elementInterface{
	function __construct($modelObj = NULL, $options = array());
}

// --- LayoutManager ---
interface Layout_managerInterface{

	function __construct($layoutName);
	//chagement de namespaces
	function changeNameSpace($controller_name);	

	//getVars
	function __set($var_name, $value);
	function __get($var_name);

	//getHelper
	function addHelper($helperName,$helperObj);
	function getHelper($helperName);
	function setTitle($title);
	function getTitle();

	//nameLayout

	function setLayoutName($layouName); 
	
	//load
	function getContent($name);
	function render();

	/*
	* @param $content_name NULL si cache global
	*/
	function saveCache($content_name = NULL);
	function loadCache($content_name = NULL);
	function deleteCache($content_name = NULL);

	//utils
	function getHeaders();
	function addHeader($headerTxt, $after = true);		
	function addCss($file, $after = true);	
	function addJs($file, $after = true);	
	
}

// --- Model ---
interface ModelInterface{
	//all data
	function setData($datas = array());
	function getData();

	//get only attr
	function __set($var_name, $value);
	function __get($var_name);
}

//Services 
interface formServiceInterface{
	function saveValidForm();
	function isValid();
}


// --- DaoFactory ---
/**
* Les connections sont fournit dans un fichier
* Dao param : $class, $elem_name, $options = array()
*/
interface daoFactoryInterface{
	function __construct($data_baseFilePath);
	function getConnection($connection_name = "default");
	function getDao($class, $elem_name, $options = array());
}


// --- DaoImpl ---
interface daoInterface{ //Note, l'id de l'elem inmodifiable
	function get($model_conditions);
	function getList($model_conditions, $options = array());
	function add($model_toAdd);
	function delete($model_toRemove);
	function update($model_toUpdate);
}
?>
