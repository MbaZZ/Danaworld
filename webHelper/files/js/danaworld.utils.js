Dispatcher.debug = true;
Dispatcher.disableJsController = false;
/**
 * Nouvelle version 5/4/2013
 * Compatible jsController
 */
/**********************/
/** Class Dispatcher **/
/**********************/
/*---------------------------------------------------**/
/**
 * Constructeur priv�
 */
function Dispatcher(){
	this.loadingBox = null;
	this.headLoaded={'css':[], 'js':[]};
	this.histoIndex = 0;
	this.request = new Array();
//	this.request[this.histoIndex] = null;
	this.response = new Array();
//	this.response[this.histoIndex] = null;
	this.controllers = {};
	if(window.onpopstate)
		window.onpopstate = function(event) {
			if(event.state){
				Dispatcher.jouerRequestAjaxHisto(event.state.histoIndex);
			}else{
				document.location.href="";
			};			
		};
}
Dispatcher.generateRequestID=function(){
	var lr=Dispatcher.getInstance().getLastResponse();
	if(lr && lr.getData('requestID')!=undefined)
		return Dispatcher.getInstance().getLastResponse().getData('requestID');
	else
		return 'i'+Math.floor((Math.random() * 100) + 1);
};
/**
 * Singleton Dispatcher
 * @returns instance de la class Dispatcher
 */
Dispatcher.instance = null;
Dispatcher.getInstance = function(){
	if(Dispatcher.instance == null){
		Dispatcher.instance = new Dispatcher();
	}
	return Dispatcher.instance;
};
/**
 * Récuperation des informations contenu dans le lanceur dans le cas d'un appel ajax
 * @param lsRequestID permet de généréer des lanceur avec des nom différents
 */
Dispatcher.prototype.getAndLoadJsControllerDatas = function(lsRequestID){
	var loDis = this;
	var loReq = new RequestControl();
	loReq.setParams('fichiers/js/launcher'+lsRequestID+'.json');
	loReq.dataType='json';
	loReq.send(function(paData){
		loDis.loadJsControllerDatas(paData);
	});
};
Dispatcher.prototype.loadJsControllerDatas = function(paDatas){
	if(Dispatcher.disableJsController==true) return;
	Utils.setLibPath(paDatas.LibPath);
	Utils.setRootPath(paDatas.RootPath);
	if(this.loadingBox==null)this.loadingBox = new LoadingBox();
	for(var lId in paDatas.controllers){
		var loController = paDatas.controllers[lId];
		Utils.addAjaxRequestToZoneIdMap(loController.ajaxZonesCorrespondances);
		var req = new RequestControl();
		req.setControllerInstanceID(loController.instanceID);
		req.isAjax = paDatas.isAjax == true;
		req.setParams(null, loController.controllerName, loController.action, loController.requestData);
		
		for(var lsformName in loController.forms){
			var loForm=loController.forms[lsformName];
			//Récupération des donnée de form si il y à
			if(loForm.datas){
				validate_util.formsData[lsformName] = loForm.datas;
								
			}
			//Gestion des elements speciaux des formulaires
			if(loForm.elementsJs){
				//Gestion des areaEditors (géré dans formHelper)
//				if(loForm.elementsJs.areaEditors){
//					
//					for(var editorId in loForm.elementsJs.areaEditors){
//						var editeur = loForm.elementsJs.areaEditors[editorId];
////						try{//Bug sous firefox : ajout d'un try catch
////							$('#'+editeur.id).elrte(editeur.arguments);
////						} catch(e){ 
////							console.log('erreur : '+e);
////						}
//						debugger
//						var editor = new wysihtml5.Editor(editeur.id, {
//							toolbar:     "wysihtml5-editor-toolbar",
//							parserRules: wysihtml5ParserRules
//						});				
//						editor.on("load", function() {
//							var composer = editor.composer;
//							composer.selection.selectNode(editor.composer.element.querySelector("h1"));
//						});
//						
//					}
//				}
				//Gestion des btn liées
				if(loForm.elementsJs.btnMultiLib){
					
					var onclik = function(event){						
						if(this.btnInfos.cacherInactif){
							var currPos=parseInt(this.btnInfos.idToPosition[this.id]);
							var nextElem=$('#'+this.btnInfos.positionToId[(currPos+1)%this.btnInfos.nbElems]);
							$(this).hide();
//							nextElem.addClass('btn_enfonce');
							nextElem.show();
							
							$('#'+this.btnInfos.hideElemID).val(nextElem.val());
						}else{
							if(this.btnInfos.btnActif == this)return false;
							var prevBtn = $(this.btnInfos.btnActif);
							prevBtn.removeClass('btn_enfonce');
							prevBtn.addClass('btn_inactif');
							$(this).addClass('btn_enfonce');
							$(this).removeClass('btn_inactif');
							this.btnInfos.btnActif = this;
							
							$('#'+this.btnInfos.hideElemID).val($(this).val());
						}
						Dispatcher.addListener(this.value, this, this.btnInfos.listnerName, this.controllerInfos.controllerName, this.controllerInfos.instanceID);
						return false;					
					};					
					for(var i in loForm.elementsJs.btnMultiLib){
						var btnGroupeInfos = loForm.elementsJs.btnMultiLib[i];
						for(var i in btnGroupeInfos.positionToId){
							var elem=document.getElementById(btnGroupeInfos.positionToId[i]);
							var lj=$(elem);
							elem.btnInfos=btnGroupeInfos;
							elem.controllerInfos=loController;
							lj.click(onclik);
							if(!btnGroupeInfos.cacherInactif){
								if(lj.hasClass('btn_enfonce')){
									btnGroupeInfos.btnActif=elem;
								}								
							} else{
								if(lj.hasClass('btn_inactif')){										
									lj.hide();									
								}
								if(lj.val() == true || lj.val() == 'true' || lj.val() == 'Oui'){
//									lj.addClass('btn-success');
									lj.addClass('btn_enfonce');
									lj.removeClass('btn_inactif');
								}else if(lj.val() == false || lj.val() == 'false' || lj.val() == 'Non'){
//									lj.addClass('btn-danger');
									lj.removeClass('btn_enfonce');
									lj.addClass('btn_inactif');
								}
							}
						}
					}					
				}				
			}
		}		
		this.loadController(new ResponseControl(req, loController.responseData, null, loController.zoneIds));		
	}
};
Dispatcher.prototype.mergeHead=function(pnew){
	for(var i in pnew.css) this.headLoaded.css.push(pnew.css[i]);
	for(var i in pnew.js) this.headLoaded.js.push(pnew.js[i]);
};
Dispatcher.prototype.getHeadsLoaded=function(){
	return this.headLoaded;
};
/**
 * Initialise si besoin et appel une action du controlleur
 * @param controllerName
 * @param actionName
 * @param data Don�es vue
 * @param idtoZoneID tableau de correspondance des �l�ments de la vue (div, liens, ...)
 * @param formData données du formulaire (message, ...) 
 */
Dispatcher.prototype.loadController = function(poReponseControl){
	//Récupération des heads
	var rep=poReponseControl.getData();
	if(rep.cssFiles!=undefined || rep.jsFiles!=undefined){
		this.mergeHead({'css':rep.cssFiles, 'js':rep.jsFiles});
	}

//	//Récupération des donnée de form si il y à
//	if(poReponseControl.formData){
//		validate_util.formsData[poReponseControl.formName] = poReponseControl.formData;
//		//Gestion des areaEditors
//		if(poReponseControl.formData.areaEditors){
//			for(var editorId in poReponseControl.formData.areaEditors){
//				var editeur = poReponseControl.formData.areaEditors[editorId];
//				try{//Bug sous firefox : ajout d'un try catch
//					$('#'+editeur.id).elrte(editeur.arguments);
//				} catch(e){ 
//					console.log('erreur : '+e);
//				}
//			}
//		}
//	}
	var lsControllerName = poReponseControl.getControllerName();
	var lsActionName = poReponseControl.getActionName();
	if(!window[lsControllerName]){
		//console.log("loadController(le contrôleur "+lsControllerName+" n'éxiste pas.");
		return;
	}
	var lsControllerIndex = lsControllerName+poReponseControl.requestControl.getControllerInstanceID();
	//console.log("loadController( "+lsControllerIndex+', '+lsActionName+')');
	this.request[this.histoIndex] = poReponseControl.requestControl;
	this.response[this.histoIndex] = poReponseControl;
//	window.history.pushState({histoIndex:this.histoIndex}, '', poReponseControl.requestControl.getHistoryUrl());
	
	if(this.controllers[lsControllerIndex] == undefined){
		//console.log("initController "+lsControllerIndex);	
		this.controllers[lsControllerIndex] =  eval('new '+lsControllerName+'(this.request[this.histoIndex], this.response[this.histoIndex]);');
	}
	this.controllers[lsControllerIndex].response = poReponseControl;
	this.controllers[lsControllerIndex].request = poReponseControl.requestControl;	
	
	
	if(this.controllers[lsControllerIndex].onReceiveNewResponse)
		this.controllers[lsControllerIndex].onReceiveNewResponse();
	if(this.controllers[lsControllerIndex].loadAction)
		this.controllers[lsControllerIndex].loadAction(lsActionName);
	else
		if(this.controllers[lsControllerIndex][lsActionName])
			this.controllers[lsControllerIndex][lsActionName]();
		else
			if(this.controllers[lsControllerIndex].actionNotFound)
				this.controllers[lsControllerIndex].actionNotFound(lsActionName);
			//else console.log("loadController("+lsControllerIndex+", l'action " + lsActionName+" n'éxiste pas, elle est ignorée");
};
Dispatcher.Events = {
		'interruptScript':0, 
		'unInterruptScript':1
		};
Dispatcher.postEvent=function(psEventName){
	var laControllers=Dispatcher.getInstance().controllers;
	for(var c in laControllers)
		if(laControllers[c].onEvent)laControllers[c].onEvent(psEventName);
};
/*
 * Gestion de la navigation par contrôlleur
 */
Dispatcher.prototype.getLastRequest=function(){
	return this.request[this.histoIndex];
};
Dispatcher.prototype.getLastResponse=function(psControllerName){
	if(psControllerName!=undefined)	return this.controllers[psControllerName].response;else return this.response[this.histoIndex];
};

/// Partie AJAX
Dispatcher.jouerUrlAjax=function(psUrl, psActionMethodeName, pfOnReceive){
	var loRequest = new RequestControl();
	loRequest.setParams(psUrl);
	loRequest.action = psActionMethodeName;
	this.jouerRequestAjax(loRequest, pfOnReceive);
};
Dispatcher.jouerActionAjax=function(psControllerName, psActionName, psActionMethodeName, pfOnReceive){
	var loRequest = new RequestControl();
	loRequest.setParams(undefined, psControllerName, psActionName);
	if(psActionMethodeName!=undefined)loRequest.action = psActionMethodeName;
	this.jouerRequestAjax(loRequest, pfOnReceive);
};
Dispatcher.jouerRequestAjaxHisto=function(pId){
	var loDi = Dispatcher.getInstance();
	loDi.histoIndex=pId;
	Dispatcher.jouerRequestAjax(loDi.request[pId]);
	
};
Dispatcher.jouerRequestAjax=function(poRequest, pfOnReceive){	
	var loDispatcher = Dispatcher.getInstance();	
	var loRequest = poRequest;
	var zone = Utils.mapAjaxRequestToZoneId[loRequest.getController()+''+loRequest.getAction()];
	if(zone == undefined) var zone = Utils.mapAjaxRequestToZoneId[loRequest.getController()+'*'];
	if(typeof zone != 'object') zone = $('#'+zone);
	if(zone.selector == "#undefined") zone = undefined;
	loDispatcher.loadingBox.show('Chargement en cours ...', zone);	
	poRequest.send(function(poResponse){
		Dispatcher.getInstance().loadingBox.hide();
		if(zone){
			var tmpZone=$(poResponse.getHtml()).children();
			var tmp2 = tmpZone.find('#'+zone.attr('id'));
			if(tmp2.length==0){
				if(tmpZone.length==0){
					tmpZone = poResponse.getHtml();
				}
			}else{
				tmpZone = tmp2.html();
			}
	//		tmpZone=tmpZone.length==0?poResponse.getHtml():tmpZone.html();
			if(tmpZone!=undefined)zone.html(tmpZone);
		}
	if(pfOnReceive)	pfOnReceive();
}, true);
};
Dispatcher.actualiseDerniereAction=function(psControllerName){
	var loDispatcher =  Dispatcher.getInstance();
	loDispatcher.loadController(loDispatcher.getLastResponse(psControllerName));
};
Dispatcher.actionPrecedente=function(loController){
//	var loDispatcher =  Dispatcher.getInstance();
	//loDispatcher.histoIndex--;
	//TODO
};
Dispatcher.actionSuivante=function(loController){
//	var loDispatcher =  Dispatcher.getInstance();
	//TODO
};
//Gestion actions interne
Dispatcher.loadListener=function(lsName, controllerName, args, controllerID){
	controllerName = controllerName.replace('_controller', '')+ '_controller';
	var lsControllerId = controllerID==undefined?controllerName+'default':controllerName+controllerID;
	return Dispatcher.getInstance().controllers[lsControllerId].listener[lsName](args);
};

Dispatcher.addListener=function(url, elem, name, controllerName, controllerID){
	if(Dispatcher.disableJsController==true) return;
	var lsControllerIndex = controllerName+controllerID;
	var loDispatcher = Dispatcher.getInstance();
	
	//Mise à jour des requêtes
	if(loDispatcher.request[loDispatcher.histoIndex].url != url){
		loDispatcher.histoIndex++;
		loDispatcher.request[loDispatcher.histoIndex] = new RequestControl();
		loDispatcher.request[loDispatcher.histoIndex].isAjax = true;
		loDispatcher.request[loDispatcher.histoIndex].setParams(url, controllerName, 'listener.'+name);
		loDispatcher.response[loDispatcher.histoIndex] = null; //Pas de reponse (configuration du lien interne javascript)
		loDispatcher.controllers[lsControllerIndex].request = loDispatcher.request[loDispatcher.histoIndex];
	}	
	if(loDispatcher.controllers[lsControllerIndex].listener && loDispatcher.controllers[lsControllerIndex].listener[name])
		return loDispatcher.controllers[lsControllerIndex].listener[name]($(elem), url);
	else{
		return true;//!Dispatcher.debug;
	}
};
/*---------------------------------------------------**/
/** Class utils **/
/*---------------------------------------------------**/
function Utils(){}
Utils.getNumber2digits=function(piNumber){
	return piNumber>9?piNumber:'0'+piNumber;
};
Utils.rootPath = '';
Utils.libPath = '';
Utils.mapZoneIdToId = {};
Utils.mapIdToZoneId = {};
Utils.mapAjaxRequestToZoneId = {};
Utils.responseData = {};
Utils.setRootPath=function(psData){
	Utils.rootPath = psData;
};
Utils.setLibPath=function(psData){
	Utils.libPath = psData;
};
Utils.setResponseData=function(paDatas){
	Utils.responseData = paDatas;
};
//Permet de définir les zone HTML correspondant aux requêtes AJAX
Utils.addAjaxRequestToZoneIdMap=function(paAjaxZoneMap){
	for(var loRequestKey in paAjaxZoneMap){
		Utils.mapAjaxRequestToZoneId[loRequestKey] = paAjaxZoneMap[loRequestKey];
	}
};
Utils.addAjaxRequestToZoneId=function(controllerName, actionName, zoneId){
	zoneId += '_'+ Math.floor((Math.random()*1000)+1);
	Utils.mapAjaxRequestToZoneId[controllerName+''+actionName] = zoneId;
	return zoneId;
};
Utils.addAjaxRequestToViewObject=function(controllerName, actionName, poViewObject){
	Utils.mapAjaxRequestToZoneId[controllerName+''+actionName] = poViewObject;
};
///////////////////////////////////////////////////////
Utils.getIdByZoneId=function(psZoneName, pZoneId){
	return Utils.mapZoneIdToId[psZoneName][parseInt(Utils.hashCode(pZoneId))];
};
Utils.getZoneIdbyId=function(psZoneName,pId){
	if(pId==undefined)pId=0;
	return Utils.mapIdToZoneId[psZoneName]?Utils.mapIdToZoneId[psZoneName][pId]:'Nothing';
};
Utils.addZoneId=function(psZoneName, pId, pZoneId){
	if(Utils.mapZoneIdToId[psZoneName] == undefined){
		Utils.mapIdToZoneId[psZoneName] = {};
		Utils.mapZoneIdToId[psZoneName] = {};
	}
	Utils.mapIdToZoneId[psZoneName][pId] = pZoneId;
	Utils.mapZoneIdToId[psZoneName][Utils.hashCode(pZoneId)] = pId;
};
Utils.hashCode = function(s){
	return s.split("").reduce(function(a,b){a=((a<<5)-a)+b.charCodeAt(0);return a&a;},0);              
};
///////////////////////////////////////////////////////
//Retourne un tab
Utils.getAppById=function(paData, pfunctiongGetId){
	if(pfunctiongGetId == undefined){
		pfunctiongGetId = function(pDataElement){
			return pDataElement.id;
		};
	}	
	var appParId = new Array();
	for(var i in paData){
		appParId[pfunctiongGetId(paData[i])] = paData[i];
	}
	return appParId;
};
Utils.createLink=function(psName, psHref, psControllerName, psListenerName){//TODO gérer les instanceID
	return '<a onclick="return Dispatcher.addListener(\''+psHref+'\', this,\''+psListenerName+'\',\''+psControllerName+'\',\'default\')" href="'+psHref+'">'+psName+'</a>';
};
//Utils.createBtn=function(psName, psControllerName, psListenerName){
//	//TODO
//};
Utils.getAbsUrl=function(psUrl){
	if(this.rootPath!='/') psUrl=psUrl.replace(this.rootPath,'');
	return this.rootPath+''+psUrl;	
};

/**
 * Class validate_util : Fonction de validation de formulaire
 */
function validate_util(poForm){
	this.form = poForm;
	this.validation = null;
	this.messagesElm = new Array();
	this.formClassName = this.form.constructor.toString().split("(")[0].split(/function\s*/)[1];
	this.modelName = this.formClassName.split('_')[0];
	var formData = validate_util.formsData[this.formClassName];
	if(formData)this.validation = formData.validation;
	this.messageToDisplay = {};
}
validate_util.formsData = {};
validate_util.prototype.setErrorMessage=function(psChampName){
	var champMessage = this.getMessageElement(psChampName);
	this.form['champ_'+psChampName].addClass('ErrorMessage');
	$('#'+this.modelName+'_'+psChampName+'Editor').addClass('ErrorMessage');//Utiliser uniquement pour l'editeur de text
	champMessage.html(this.messageToDisplay[psChampName]);
	champMessage.slideDown();
};
validate_util.prototype.unsetErrorMessage=function(psChampName){
	var champMessage = this.getMessageElement(psChampName);
	champMessage.fadeOut();
	this.form['champ_'+psChampName].removeClass('ErrorMessage');
};
validate_util.prototype.getMessageElement=function(psChampName){
	if(this.messagesElm[psChampName] == undefined)
		this.messagesElm[psChampName] = $("#"+this.modelName+'_'+psChampName+"Message");
	
	return this.messagesElm[psChampName];
};

//Vérifie si le formulaire est valide (en fonction de la variable $validate déclarée dans le model
validate_util.prototype.isValide=function(){
	var lbReturn = true;
	this.messageToDisplay = new Array();
	if(this.validation != null){
		for(var poChampName in this.validation){
			var poChamp = this.form['champ_'+poChampName];
			if(poChamp != undefined){
				var validation = this.validation[poChampName];
				if(validation != undefined){					
					if(validation instanceof Object){
						for(var i in validation){
//							var pfValidator = eval('validate_util.'+validation[i]);
							var pfValidator = validate_util[validation[i]];	
							if(pfValidator != undefined){
								var retourMessage = pfValidator(poChamp.val());
								if(retourMessage != true){
									this.messageToDisplay[poChampName] = retourMessage;
									lbReturn = false;
								}
							}else{
								//Cas fonction de validation introuvable
								if(Dispatcher.debug){
									console.log('Validation de '+poChampName+' impossible');
									lbReturn = false;
								}//sinon : le formulaire sera envoyé puis validé coté serveur
							}
						}
					}else if(typeof validation == 'string' && poChamp.val().trim() == ''){//Si un message sans tableau alors c'est un champs obligatoire
						this.messageToDisplay[poChampName] = validation;
						lbReturn = false;
					}
				}
			}
		}
	}	
	return lbReturn; //La validation est OK
};
validate_util.prototype.afficherErreurMessage=function(){
	for(var champName in this.messageToDisplay)
		this.setErrorMessage(champName);
};
validate_util.prototype.effacerErreurMessage=function(OPTpsMessageName){
	for(var elem in this.messagesElm)
		this.unsetErrorMessage(elem);
};
validate_util.prototype.getErrorMessages=function(OPTpsMessageName){
	if(OPTpsMessageName != undefined)
		return this.messageToDisplay[OPTpsMessageName];
	else
		return this.messageToDisplay;
};
validate_util.prototype.getStrErrorMessages=function(){
	var str = '';
	for(var i in this.messageToDisplay)
		str += this.messageToDisplay[i] + '\n';
	return str;
};
validate_util.setFormData=function(poData){
	this.messages = poData.messages;
	this.validate = poData.validate;
};
validate_util.validMail=function(psMail){
	return 'E-mail incorrecte !';
};
/*---------------------------------------------------**/
/*
 * Paramètres des actions des controller js
 */
function RequestControl(){
	this.url = null;
	this.controller = null;
	this.action = null;
	this.data = {};
	this.isAjax = false;
	this.dataType = 'html';
}

RequestControl.prototype.getUrl = function(){
	return this.url;
};
RequestControl.prototype.getAjaxUrl = function(){
	return this.url.substr(3, this.url.length);
};
RequestControl.prototype.setHistoryUrl=function(sUrl){
	this.data.historyUrl=sUrl;
};
RequestControl.prototype.getHistoryUrl = function(){
	return this.data.historyUrl?this.data.historyUrl:this.url;
};
RequestControl.prototype.getController = function(){
	return this.controller;
};
RequestControl.prototype.getAction = function(){
	return this.action;
};
RequestControl.prototype.getData = function(cle){
	return this.data[cle];
};
RequestControl.prototype.getHtml = function(){
	return this.html;
};
RequestControl.prototype.setParams=function(psUrl, pController, pAction, pData){
	this.url = this.setUrl(psUrl);
	this.controller = pController;
	this.action = pAction;
	this.data = pData;//==null||pData==undefined?new Array():pData;
	this.controllerInstanceID='default';
	this.autoSet();
};
/**
 * Permet de gérer plusieurs instances d'un controleur si utilisé
 * @param psInstanceID
 */
RequestControl.prototype.setControllerInstanceID=function(psInstanceID){
	this.controllerInstanceID = psInstanceID;
};
RequestControl.prototype.getControllerInstanceID=function(){
	return this.controllerInstanceID;
};
RequestControl.prototype.addData=function(psName, pValue){
	this.data[psName] = pValue;
};
RequestControl.prototype.addFormDatas=function(poFormHtml){
	var data = poFormHtml.serializeArray();
	for(var i in data){
		this.data[data[i].name] = data[i].value;
	}
};
RequestControl.prototype.setUrl=function(psUrl){
	return psUrl!=null?Utils.getAbsUrl(psUrl):null;
};

/**
 * Envoi la requête en AJAX et créé un objet reponse
 * @param pfOnResponse (opt) pointeur vers la fonction appellée une fois la réponse arrivée
 * @param pbLoadControllerAction (opt=false) charge le launcher une fois la réponse arrivée
 */
RequestControl.prototype.send=function(pfOnResponse, pbLoadControllerAction){
	this.isAjax = true;
	this.addData('ControllerInstanceID', this.controllerInstanceID);
	this.addData('request', 'ajax');
	var requestID = Dispatcher.generateRequestID();
	this.addData('requestID', requestID);
	if(pbLoadControllerAction == undefined) pbLoadControllerAction = false;
	var loRequest = this;
	var links = $("link, script");
	this.addData('linkLoaded' , Dispatcher.getInstance().getHeadsLoaded());
	$.ajax( {
		type: "post",
		data: loRequest.data,
		url: loRequest.getUrl(),
		dataType: loRequest.dataType,
		success: function(pHtml) { 
			if(loRequest.dataType == 'html'){
				//Merge des en-têtes
				loHtml = document.createElement("html");
				loHtml.innerHTML = pHtml;
				loHtml = $(loHtml);
				var newheads = loHtml.children('head').children('link, script');
				for(var i =0 ;i < links.length; i++){
					for(var j = 0 ; j < newheads.length; j ++){
						if((links[i].href != undefined && links[i].href == newheads[j].href)
							|| (links[i].src != undefined && links[i].src == newheads[j].src)){
							newheads[j] = '';//on enleve les nouveaux éléments link déja chargés 
						}	
					}
				}				
				//Artifice de syncro (car certains navigatueur ne veulent pas de synchrone ici alors que ca ne change rien pour l'utilisateur car listener asynchrone a la base
				var nbHeadersToSync = 0;
				//Ajout des nouveau links non doublé aux anciens chargés
				for(var i = 0 ; i < newheads.length; i++){
					if(newheads[i] != ''){
						$("head").append(newheads[i]);
						if(newheads[i].tagName == 'LINK'){
							nbHeadersToSync++;
							newheads[i].onload=function(){nbHeadersToSync--;};
						}							
					}
				}						
			
				//On enleve le lanceur initial
				$("script[src='/web/accueilServer/fichiers/js/launcher.js']").remove();
				
				var content = loHtml.children("body").children("#content").children();
				//Construction de la réponse
				var loAjaxResponse = new ResponseControl(loRequest, undefined, content);
	
				//charge la fonction passée en paramètres une fois que les head ont été récupérés				
				function wait4Action(){
					if(nbHeadersToSync>0){
						setTimeout(function(){
							wait4Action();
						}, 20);
					}else{
				    	if(pfOnResponse != undefined)
				    		pfOnResponse(loAjaxResponse);
				    	
				    	//Lance l'action si spécfier en paramètre
						if(pbLoadControllerAction){
							Dispatcher.getInstance().getAndLoadJsControllerDatas(requestID);
							//Gestion du cas ou le controller charge un autre controller : il faut charger les 2 controllers
							var loRequestImbrique = new RequestControl();
							loRequestImbrique.setParams(loRequest.getUrl());
							if(loRequestImbrique.getController() != loRequest.getController()){
								Dispatcher.getInstance().loadController(loAjaxResponse);
							}
						}
				    }										
				}			
				wait4Action();//Lance les actions une fois les fichiers recupéré
			}else{
				//charge la fonction passée en paramètres
				if(pfOnResponse != undefined)
					pfOnResponse(pHtml);				
			}
		}
	});
};
RequestControl.prototype.autoSet=function(){
	if(this.url == null){
		if(this.action != null && this.controller != null)
			this.url = Utils.getAbsUrl(this.controller.replace('_controller', '')+'/'+this.action); 
		else
			this.url = document.URL;
	}
	if(this.action == undefined || this.action == null){
		var splitUrl = this.url.split('/');
		if(splitUrl.length > 2){		
			this.action = splitUrl[splitUrl.length-1].split('?')[0];
			this.controller = splitUrl[splitUrl.length-2]+'_controller';
		}
	}
	if(this.data == undefined || this.data == null){		
		var splitUrl = this.url.split('?');
		this.data = {};
		if(splitUrl.length > 1){
			splitUrl = splitUrl[1].split('&');
			for(var param in splitUrl){
				var nameValue = splitUrl[param].split('=');
				this.data[nameValue[0].replace('%20',' ')] = nameValue[1].replace('%20',' ');
			}
		}
	}
};
/*---------------------------------------------------**/
function ResponseControl(poRequestControl, paData, poHtml, paidtoZoneID, psFormName, paFormData){
	this.requestControl = poRequestControl;
	this.html = poHtml;	
	this.data = paData!=null&&paData!=undefined?paData:{};		
	if(paidtoZoneID){
		this.htmlZoneIdMap = paidtoZoneID;
		for(var lsZone in this.htmlZoneIdMap){
			var laZones = this.htmlZoneIdMap[lsZone];
			for(var id in laZones){
				id = parseInt(id);
				Utils.addZoneId(lsZone, id, laZones[id]);
			}
		}
	}
	
	this.formData = paFormData;
	this.formName = psFormName;
}
ResponseControl.prototype.getControllerName = function(){
	return this.requestControl.controller;
};
ResponseControl.prototype.getActionName = function(){
	return this.requestControl.action;
};
ResponseControl.prototype.getFormData = function(){
	return this.formData;
};
ResponseControl.prototype.getData = function(psId){
	return psId==undefined?this.data:this.data[psId];
};
ResponseControl.prototype.getHtml = function(){
	return this.html;
};
ResponseControl.prototype.getHtmlZoneIdMap = function(){
	return this.htmlZoneIdMap;
};
/*---------------------------------------------------**/
function basename(path) {
	if(path == null)
		return "";
    return "|-|"+path.replace(/\\/g,'/').replace(/.*\//, '' );
}
