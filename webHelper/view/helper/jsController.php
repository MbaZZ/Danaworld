<?php
require_once "view/helper/form.php";

class jsController extends form
{
	private $instanceID;
	private $requestID;
	private $html; //Layout
	private $zoneIds;//Tableau des id des zones html classées par id en lien avec les données
	private $ajaxZonesCorrespondances = array();
	private $isAjaxRequest = false;
	
	
	public function __construct($html){
		Logger::tracerInfo("jsControllerHelper");
		$this->html = $html;
		if(isset($_POST['ControllerInstanceID'])){
			$this->instanceID = $_POST['ControllerInstanceID'];
		}elseif(isset($_GET['ControllerInstanceID'])){
			$this->instanceID = $_GET['ControllerInstanceID'];
		}else{
			$this->instanceID = 'default';
		}	
		$this->isAjaxRequest =isset($_POST['request']) && $_POST['request'] == 'ajax' || isset($_GET['request']) && $_GET['request'] == 'ajax';
		
		if($this->isAjaxRequest && isset($_POST['requestID'])){
			$this->requestID = $_POST['requestID'];
		}elseif($this->isAjaxRequest && isset($_GET['requestID'])){
			$this->requestID = $_GET['requestID'];
		}else{
			$this->requestID = router::getRequestID(); //TODO debug lorsque appellé directement par javscript
		}	
	}
	
	/************************ Fonctions de vue ************************/
	private function getListnerName($name, &$args){
		if(isset($args['lanceurJS'])){
			$listenerName = $args['lanceurJS'];
			unset($args['lanceurJS']);
		}elseif(isset($args['title'])){
			$listenerName = $args['title'];
		}else{
			$listenerName = $name;
		}
		return lcfirst(str_replace('�','',str_replace(array(' ', ':','é','è','à'), '',$listenerName)));
	}
	
	/**
	 * Retourne un lien HTML en chaine de caractères
	 * @param string $name Libellé du lien
	 * @param array $conditions attributs du lien
	 */
	public function link($name,$conditions = array()){
		//Gestion des param�tre (valeur par default, etc...)
		$conditionsString = "";
		$onclick = "";
		$href = "";
		$libel=$name;
		if(isset($conditions['href'])){
			$conditions['href'] = ROOT.$conditions['href'];
			$href = $conditions['href'];
		}
		$listenerName = $this->getListnerName($name, $conditions);
		if(!isset($conditions['id'])){
			$conditions['id'] = $listenerName.rand();
		}
		if(isset($conditions['onclick'])){
			$onclick = $conditions['onclick'];
		}
		if(isset($conditions['libel'])){
			$libel = $conditions['libel'];
			unset($conditions['libel']);
		}
		
		//Cr�ation du code HTML
// 		foreach($conditions as $key => $val){
// 			$conditionsString .= $key.'="'.$val.'" ';		
// 		}		
		$conditionsString = parent::getListArguments($name, $conditions);
		//Séparation de l'url de ses paramètres	
		$this->writeAction('link', $listenerName,  $href);
		$sOnclick = 'onclick="return Dispatcher.addListener(\''.$href.'\', this, \''.$listenerName.'\',\''.$this->phpControllerClassName.'\',\''.$this->instanceID.'\')'.($onclick!=''?'&&'.$onclick:'');
		$onclick = $sOnclick.'"';
		$res = "<a $onclick $conditionsString><span>$libel</span></a>";
		
		return $res;
	}
	/**
	 * Ajoute un identifiant de zone relatif à l'instance du helper jsController
	 * Retourne l'id absolue de cette zone
	 * @param string $zoneName Nom de la zone
	 * @param string $id Identifiant si il s'agit d'une sous-zone
	 * @param string $chaineId chaine supplémentaire à utiliser pour généré l'id
	 */
	public function addElemAndGetId($zoneName, $id = 0, $chaineId = ""){
		$idGen = $zoneName.$this->instanceID.$chaineId.$id;
		$this->zoneIds[$zoneName][$id] = $idGen;					
		return $idGen;
	}
	
	public function addAjaxZoneElemAndGetId($actionName, $zoneName="default", $id = 0, $chaineId = ""){
		$id = $this->addElemAndGetId($zoneName, $id, $chaineId);
		$this->ajaxZonesCorrespondances[$this->phpControllerClassName.$actionName] = $id;
		return $id;
	}
	/************************ Helper Form ************************/
	//private $modelName;
	private $jsForms = ""; //code js a g�n�r�
	private $formData = array(); //messages � passer � js + donn�e de validation
	private $formElementsJs=array();
	private $endl = "\n";
	private $formName = 'form';
	private $formAction='';
	public function getValidForm(){
		return parent::getValidForm();
	}
	public function setMessage($name, $val){
		$this->formData['messages'][$name] = $val;
		parent::setMessage($name, $val);
	}
	public function setModel($model){
		parent::setModel($model);
		$this->formName = $this->modelName.'_form';
		$this->formData['validation'] = $model->validates; //Donn�e de validation
		$this->formData['messagesZoneId'][] = "messagfze TODO";
	}	
	public function openForm($arguments = array()){
		if(isset($arguments['action'])) $this->formAction = $arguments['action'];
		Logger::tracerInfo('Form open jsControllerHelper');
		$this->champsObligatoires = array(); //Desactive les validations cote client fait par HTML5 car l'information de validation n'arrive qu'au fur et a mesure
		if(!file_exists($this->jsControllerFilePath) || $this->forceGenerate){
			if(!isset($arguments['id'])) $arguments['id'] = $this->formName.'ID';
			$endl = "\n";
			$this->jsForms .= '//------------------------------------'.$endl;
			$this->jsForms .= '// Formulaire javascript'.$endl;
			$this->jsForms .= '//------------------------------------'.$endl;
			$this->jsForms .= 'function '.$this->formName.'(){'.$endl;	
			$this->jsForms .= '    this.validatation = new validate_util(this);'.$endl;
			$this->jsForms .= '    this.'.$this->formName.'Html = $("#'.$arguments['id'].'");'.$endl;
		}
		return parent::openForm($arguments);
	}
	public function end(){
		$endl = "\n";
		$this->jsForms .= '};'.$endl;
		return parent::end();
	}
	protected function messageHtml($name){
		$validError = validate_util::getMessage($name);
		$message = "<div class='required' id='".$this->modelName."_".$name."Message' >";
		if($validError){
            $message .= $validError;
		}
		$message .= "</div>";
		return $message;
	}
	public function label($name,$label){
		if(!file_exists($this->jsControllerFilePath) || $this->forceGenerate)
			$this->jsForms .= '    this.'.$name.'Label = $("#'.$this->modelName.'_'.$name.'Label");'.$this->endl;
		return parent::label($name,$label);
	}
	public function message($name){
		$this->formData['messagesZoneId'][] = $name;
		return parent::message($name);
	}
	public function textarea($name,$arguments = array()){
		if(!file_exists($this->jsControllerFilePath) || $this->forceGenerate)
			$this->jsForms .= '    this.champ_'.$name.' = $("#'.$this->modelName.'_'.$name.'");'.$this->endl;
		return parent::textarea($name,$arguments);
	}
	public function areaeditor($name, $arguments = array()){
		if(!file_exists($this->jsControllerFilePath) || $this->forceGenerate)
			$this->jsForms .= '    this.champ_'.$name.' = $("#'.$this->modelName.'_'.$name.'");'.$this->endl;		
// 		$arguments['id'] = $this->modelName."_".$name;
// 		$newAreaEditor['id'] = $arguments['id'];
// 		$this->formElementsJs['areaEditors'][] = $newAreaEditor;		
		return parent::areaeditor($name, $arguments, true);
	}
	
	public function select($name,$arguments = array()){
		if(!file_exists($this->jsControllerFilePath) || $this->forceGenerate)
			$this->jsForms .= '    this.'.$name.'Select = $("#'.$this->modelName.'_'.$name.'");'.$this->endl;		
		return parent::select($name,$arguments);
	}
	//créé plusieurs select liées (fonctionne avec les mêmes donneés que les categories) (TODO)
// 	public function selectLiee($names,$arguments = array()){
// 		$arguments['noCategorie'] = true;
// 		$return = $this->select($names[0], $arguments);
// 		$arguments['option'] = array('----');
// 		$length=count($names);
// 		for($i=1;$i<$length;$i++){
// 			$return .= $this->select($names[$i], $arguments);
// 		}
// 		return $return;
// 	}
	public function input($name,$arguments = array()){
		if(!isset($arguments['id']))
			$arguments['id'] = $this->modelName."_".$name;
		if(!file_exists($this->jsControllerFilePath) || $this->forceGenerate)
			$this->jsForms .= '    this.champ_'.$name.' = $("#'.$arguments['id'].'");'.$this->endl;
		return parent::input($name,$arguments);
	}
	public function password($name,$arguments = array()){
		if(!isset($arguments['id']))
			$arguments['id'] = $this->modelName."_".$name;
		if(!file_exists($this->jsControllerFilePath) || $this->forceGenerate)
			$this->jsForms .= '    this.champ_'.$name.' = $("#'.$arguments['id'].'");'.$this->endl;
		return parent::password($name,$arguments);
	}
	public function submit($name,$arguments = array()){
		if(!isset($arguments['id']))
			$arguments['id'] = $this->modelName."_".$name;
			
		$this->jsForms .= '    this.'.$name.'Submit = $("#'.$arguments['id'].'");'.$this->endl;
		return parent::submit($name,$arguments);
	}	
	public function file($name,$arguments = array()){
		return parent::file($name,$arguments);
	}
	public function button($name,$value, $content,$arguments = array()){
		if(!isset($arguments['id']))
			$arguments['id'] = $this->modelName."_".$name;
		$this->jsForms .= '    this.'.$name.'Button = $("#'.$arguments['id'].'");'.$this->endl;

		$listnerName = $this->getListnerName($name, $arguments);
		
		$result='';
		if(is_array($value) && is_array($content)){
			$infosElem = array(
					'name' => $name,
					'listnerName' => $listnerName,
					'values' => $value,
					'contents' => $content,
					'positionToId' => array(),
					'idToPosition' => array(),
					'nbElems' => 0,
					'cacherInactif' => true,
					'hideElemID' => $arguments['id'].'hid'
			);
			foreach($value as $id => $val){
				$arguments['id'] = $this->modelName."_".$name.$id;
				$infosElem['positionToId'][$id] = $arguments['id'];
				$infosElem['idToPosition'][$arguments['id']] = $id;
				$result.=parent::button($name,$val, $content[$id],$arguments, $this->modelName."_".$name);
				$infosElem['nbElems'] += 1;
			}
			
		}else{
			if(isset($this->formElementsJs['btnMultiLib'][$name])){
				$infosElem=$this->formElementsJs['btnMultiLib'][$name];	
			}else{
				$infosElem = array(
					'name' => $name,
					'listnerName' => $listnerName,
					'values' => array(),
					'contents' => array(),
					'positionToId' => array(),
					'idToPosition' => array(),
					'nbElems' => 0,
					'cacherInactif' => false,
					'hideElemID' => $arguments['id'].'hid'
				);
			}
			$idHid=$arguments['id'];
			$arguments['id'] = $arguments['id'].$infosElem['nbElems'];
			$infosElem['values'][] = $value;
			$infosElem['contents'][] = $content;
			$infosElem['positionToId'][$infosElem['nbElems']] = $arguments['id'];
			$infosElem['idToPosition'][$arguments['id']] = $infosElem['nbElems'];
			$infosElem['nbElems']++;	
			
			$result = parent::button($name,$value, $content,$arguments, $idHid);			
		}
		
		$this->formElementsJs['btnMultiLib'][$name] = $infosElem;
		return $result;
	}
	public function radio($name,$arguments = array()){
		return parent::radio($name,$arguments);
	}
	public function checkbox($name,$arguments = array()){
		return parent::checkbox($name,$arguments);
	}
	
	
	/************************ Section génération du controller js ************************/
		
	private $jsScriptInViewElement; //Contient le code JS à générer si besoin
	private $endJscript = "";
	private $jsControllerFilePath;
	private $jsListener = "";
	private $forceGenerate = false;
	private $controllerNamePart1;
	private $phpControllerClassName;
	private $controllerObject;
	private $lanceurJS;	
	
	/**
	 * Initialise une copie javascript du contrôleur php
	 * La vue est généré si le fichier n'existe pas et si la méthode generate est appellée
	 * @param unknown_type $phpController
	 */
	public function initJSController($phpController){
		$this->controllerObject = $phpController;
// 		$phpControllerClassName = strtolower(get_class($phpController));
		$phpControllerClassName = get_class($phpController);
		$this->phpControllerClassName = $phpControllerClassName;
		$endl = "\n";
		$indent = '    ';
		$js = "";
		$this->controllerNamePart1 = substr($phpControllerClassName,0,-11);
// 		$jsControllerFilePath =$this->controllerNamePart1 .'/'.strtolower($phpControllerClassName);
		$jsControllerFilePath =$this->controllerNamePart1 .'/'.$phpControllerClassName;
		$this->html->addJs($jsControllerFilePath);
		if($this->isAjaxRequest == false)		
			$this->html->addJs('launcher'.$this->requestID,true);	
					
		//Generation du controleur js
		$this->jsControllerFilePath = 'files/js/'.$jsControllerFilePath.'.js';		
		
		if(!file_exists($this->jsControllerFilePath) || $this->forceGenerate){
			Logger::tracerInfo("generate new jsControllerFile");
			Logger::tracerInfo('G�n�ration du fichier '.$jsControllerFilePath.'.js');
			$this->jsControllerFilePath = '/var/www'.ROOT.'files/js/'.$jsControllerFilePath.'.js';
			//TODO changer chemin
			$js .= '//------------------------------------'.$endl;
			$js .= '// Contr�leur javascript'.$endl;
			$js .= '//------------------------------------'.$endl;
			$js .= "function ".$phpControllerClassName."(request, response){".$endl;
			$js .= $indent.'this.request = request;'.$endl;
			$js .= $indent.'this.response = response;'.$endl;
			$js .= $indent.'this.view = new '.$this->controllerNamePart1.'View();'.$endl;
			$js .= $indent.'this.listener = new '.$this->controllerNamePart1.'Listener(this);'.$endl;
			$js .= '}'.$endl;
			

			$js .= '// M�thode appell�e lors de r�ception de chaque r�ponse'.$endl;
			$js .= $phpControllerClassName.'.prototype.onReceiveNewResponse=function(){'.$endl;
			$js .= 'if(this.response.htmlZoneIdMap && this.response.getControllerName() == "'.$phpControllerClassName.'")'.$endl;
			$js .= $indent.'this.view.mapIdToIdZone = this.response.htmlZoneIdMap;'.$endl;
			$js .= '};'.$endl;
					
			$class_methods = get_class_methods($phpControllerClassName);
			$metier_js = '';
			foreach($class_methods as $methodeName){
				if(array_diff(array($methodeName), array('abstractController', 
														 'loadAction', 
														 'appController', 
														 'getInterceptors',
														 'actionNotFound',
														 'getCurrentActionName' )) 
					&& substr($methodeName, 0,2) != "__"){
					
					$js .= $phpControllerClassName.'.prototype.'.$methodeName.'=function(){'.$endl;
					$js .= $indent.'this.view.'.$methodeName.'(this.request.getUrl());'.$endl;
					$js .= '};'.$endl;
					
					$metier_js .= $this->controllerNamePart1.'View.prototype.'.$methodeName.'=function(data){'.$endl;
					$metier_js .= $endl;
					$metier_js .= '};'.$endl;
				}
			}
			//Initialisation de la calsse listener
			$js .= '//------------------------------------'.$endl;
			$js .= '// Listener'.$endl;
			$js .= '//------------------------------------'.$endl;
			$js .= 'function '.$this->controllerNamePart1.'Listener(controller){'.$endl;	
			$js .= $indent.'this.controller = controller;'.$endl;
			$js .= "}".$endl;
						
			//Generation de la partie vue	
			$js .= '//------------------------------------'.$endl;
			$js .= '// Accès aux éléments de la vue'.$endl;
			$js .= '//------------------------------------'.$endl;
			$js .= 'function '.$this->controllerNamePart1.'View(){'.$endl;			
// 			$js .= $indent.'this.mapIdToIdZone;//Correspondance id / vues'.$endl;
			$this->jsScriptInViewElement = $js;			
			$js = "}".$endl;
			$js .= $metier_js;	
			$this->endJscript = $js;			
		}		
	}	
	
	//Ajoute un nouveau listener
	private $ListenerGenere = array();
	public function writeAction($sType, $name, $url){
		if(($this->jsScriptInViewElement != NULL && $this->jsControllerFilePath != NULL || $this->forceGenerate) && !isset($this->ListenerGenere[$name])){
			$endl = "\n";
			$indent = '    '; 
			$this->jsListener .= $this->controllerNamePart1.'Listener.prototype.'.str_replace(' ', '', $name).'=function(oElem, sUrl){'.$endl;
			$this->jsListener .= $indent.'alert("Action du lien '.$name.' url:\''.$url.'\'");'.$endl;
			$this->jsListener .= $indent.'return true;//Autorise le suivi du lien'.$endl;
			$this->jsListener .= '};'.$endl;
			$this->ListenerGenere[$name] = true;
		}
	}	
	
	//Methodes utilitaires
	private function generateJsonParam($url){		
		if(!isset($params[1]))
			return '{}';
		$params = explode('&', $params[1]);
		$retour = '{';
		foreach($params as $p){
			$retour .= str_replace('=',':\'',$p.'\'').',';
		}
		if($retour!='{')
			$retour = substr($retour,0,-1);
		$retour .= '}';
		return $retour;
	}
	
	public function __destruct(){	
		Logger::tracerErreur('Je ne comprend vraiement rien');
		if($this->jsScriptInViewElement != NULL && $this->jsControllerFilePath != NULL && $this->jsControllerFilePath != ""|| $this->forceGenerate)
			file_put_contents($this->jsControllerFilePath, $this->jsScriptInViewElement.''.$this->endJscript.''.$this->jsListener.''.$this->jsForms);
		else
			Logger::tracerErreur("jsControllerHelper - nom de fichier incorrecte (".$this->jsControllerFilePath.")");
			
		/*$formData = 'null';
		//Ajout des donn�es du formulaire
		if($this->formData != array()){
			$formData[] = '{\''.$this->formName.'\':'.json_encode($this->formData).'}';
		}	
		*/
		//Generation du lanceur	 : Nouvelle implementation du lanceur : format JSON uniquement
		Logger::tracerInfo("G�n�ration du lanceur JS");
		if(!isset($GLOBALS['content.launcher.isLoaded'])){
			//Cas Première instance de jsControllerHelper
			$GLOBALS['content.launcher.isLoaded'] = true;
			$_SESSION['content.fichier.launcher'.$this->requestID] = NULL;
			//variables globals aux contrôleurs
			$_SESSION['content.fichier.launcher'.$this->requestID]['RootPath'] = ROOT;
			$_SESSION['content.fichier.launcher'.$this->requestID]['LibPath'] = LIB;
			$_SESSION['content.fichier.launcher'.$this->requestID]['isAjax'] = $this->isAjaxRequest?true:false;
		}
		
		//Variables locales à chaque contrôleur
		$current_controller = Array();
		$current_controller['responseData'] = $this->html->getVars();
		$current_controller['requestData'] = array_merge($_POST, $_GET);
// 		$current_controller['formName'] = $this->formName;
// 		$current_controller['formData'][$this->formName] = $this->formData;
		$current_controller['forms'][$this->formName]['datas'] = $this->formData;
		$current_controller['forms'][$this->formName]['elementsJs'] = $this->formElementsJs;
		
		$current_controller['controllerName'] = $this->phpControllerClassName;
		$current_controller['action'] = $this->controllerObject->getCurrentActionName();
		$current_controller['instanceID'] = $this->instanceID;
		$current_controller['zoneIds'] = is_array($this->zoneIds)?$this->zoneIds:array();
		$current_controller['ajaxZonesCorrespondances'] = $this->ajaxZonesCorrespondances;
		$_SESSION['content.fichier.launcher'.$this->requestID]['controllers'][] = $current_controller;
		
	}
	
}
?>

