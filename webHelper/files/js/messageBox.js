/**
 * Class MessageBox.
 * Permet de créer une boite de dialogue 
 */
function MessageBox(){
	this.createCentredElement();
}
MessageBox.prototype.createCentredElement=function(){
	//Elements visuels
	this.divCadre = $("<div>" ,{'class':'messageBoxGlobal'});
	this.divHideBody = $("<div>" ,{'class':'messageBoxBody'});
	this.divTitle = $("<div>" ,{'class':'msgWarning messageBoxTitle'});
	this.divContent = $("<div>" ,{'class':'segmentBox messageBoxContent'});
	this.divBas = $("<div>" ,{'class':'segmentBox divBas'});
	
	this.divBtnClose = $("<div>", {
		'class':'divBtnClose'
	});
	this.btnClose = $("<input>", {
		'type':'submit',
		'value':'Fermer',
		'class':'closeBoxBtn'
	});
	this.divBtnClose.html(this.btnClose);
	
	//Initialisation des elements
	this.divCadre.hide();	
	this.divHideBody.hide();
	this.divCadre.html(this.divTitle);
	this.divCadre.append(this.divContent);
	this.divCadre.append(this.divBas);
	this.divCadre.append(this.divBtnClose);
	$("body").append(this.divHideBody);
	$("body").append(this.divCadre);
	
	var loBox = this;
	this.divHideBody.click(function(){
		loBox.hide();
	});
	
	this.btnClose.click(function(){		
		loBox.hide();
	});
};
MessageBox.prototype.centrerBox=function(){
	var xPos = parseFloat(this.divHideBody.css('width')) / 2.0 - parseFloat(this.divCadre.css('width')) / 2.0;
	var yPos = parseFloat(this.divHideBody.css('height')) / 2.2 - parseFloat(this.divCadre.css('height')) / 2.0;
	if(yPos < 0) yPos = 0;
	//centrage automatique
	this.divCadre.css({
		'top':yPos+'px',
		'left':xPos+'px'
	});	
};
/**
 * Affiche la boite de dialogue.
 */
MessageBox.prototype.show = function (){
	this.centrerBox();
	$("body").addClass("bodyHided");
	this.divHideBody.show();
	this.divCadre.show();
};
/**
 * ferme la boite de dialogue.
 */
MessageBox.prototype.hide = function (){
	$("body").removeClass("bodyHided");
	this.divHideBody.hide();
	this.divCadre.hide();
};
/**
 * Définit le contenu de la boite de dialogue.
 * @param pContent contenu à afficher
 * @param psTitle titre
 */
MessageBox.prototype.setContent = function (pContent, psTitle){
	if(psTitle == undefined){
		psTitle = "MessageBox.title.default";
	}
	this.divTitle.html(psTitle);
	if(typeof(pContent)=='string'){
		this.divContent.html("<p>"+pContent+"</p>");	
	}else{
		this.divContent.html(pContent);	
	}
};
MessageBox.prototype.addContent = function(pContent){
	if(typeof(pContent)=='string'){
		this.divContent.append("<p>"+pContent+"</p>");	
	}else{
		this.divContent.append(pContent);	
	}
};
//var QuestionLeftBox=QuestionBox;
function QuestionLeftBox(){this.createCentredElement(); this.divCadre.addClass('bandeauVertical');}
QuestionLeftBox.prototype= Object.create(QuestionBox.prototype);
QuestionLeftBox.prototype.centrerBox=function(){
	var xPos = 0;
	var yPos = 0;//parseFloat(this.divHideBody.css('height')) / 2.2 - parseFloat(this.divCadre.css('height')) / 2.0;
	if(yPos < 0) yPos = 0;
	//centrage automatique
	this.divCadre.css({
		'top':yPos+'px',
		'left':xPos+'px'
	});
};
//QuestionLeftBox.prototype.setImbrication=function(poElem){
//	var yPos=poElem[0].offsetParent.offesetRight;
//	this.divCadre.css('top',yPos);
//	this.divCadre.css('bottom',yPos + parseInt(poElem.css('height')));
//}
/**
 * Class QuestionBox.
 * Permet de poser une question à l'utilisateur et d'appeller un listener en fonction
 */
function QuestionBox(){	this.createCentredElement(); }
QuestionBox.prototype.createCentredElement=function(){
	//Elements visuels
	this.divCadre = $("<div>" ,{'class':'messageBoxGlobal'});
	this.divHideBody = $("<div>" ,{'class':'messageBoxBody'});
	this.divTitle = $("<div>" ,{'class':'msgWarning messageBoxTitle'});
	this.divContent = $("<div>" ,{'class':'segmentBox messageBoxContent'});
		
	//Initialisation des elements
	this.divCadre.hide();	
	this.divHideBody.hide();
	this.divCadre.html(this.divTitle);
	this.divCadre.append(this.divContent);
//	this.divCadre.append(this.divBtnClose);
	$("body").append(this.divHideBody);
	$("body").append(this.divCadre);
	
	this.focusElem = null;
	this.nbQuestion=0;
	this.exitBtn = null;
	this.bufferBtn=null;
	this.firstQzone=null;
}
QuestionBox.prototype.setTitle=function(psTitle){
	if(psTitle == undefined){
		psTitle = "MessageBox.title.default";
	}
	this.divTitle.html(psTitle);
};
QuestionBox.prototype.addQuestion=function(psQuestion, paReponses, psListenerName, psControllerName, optpiSelected){
	if(optpiSelected==undefined){
		optpiSelected = 0;
	}
	this.nbQuestion++;
	var qZone = $('<div>');
	var qZoneBoutton = $("<div>", {
		'class':'divBtnQuestion'
	});
	if(typeof(pContent)=='string'){
		qZone.html("<p>"+psQuestion+"</p>");	
	}else{
		qZone.html(psQuestion);	
	}
	if(this.nbQuestion==1){//Differe l'insertion des btn en cas de monoquestion
		this.bufferBtn = qZoneBoutton;
		this.firstQzone = qZone;
	}else{
		if(this.nbQuestion==2)this.firstQzone.append(this.bufferBtn);			
		qZone.append(qZoneBoutton);
	}
		
	loBox = this;
	var laBtn = [];
	var listener = function(){
		Dispatcher.loadListener(psListenerName, psControllerName, this.datas);
		if(loBox.nbQuestion>1){			
			for(var i in laBtn)laBtn[i].removeClass('closeBoxBtnSelected');
			$(this).addClass('closeBoxBtnSelected');
		}else loBox.hide();
	};
	var i = 0;
	for(var r in paReponses){
		var btn = $("<input>", {
			'type':'submit',
			'value':paReponses[r],
			'class':'closeBoxBtn'
			});
		btn[0].datas = {'idClic':r,'libelClic':paReponses[r]};
		btn.click(listener);
		laBtn.push(btn);
		if(i==optpiSelected){
			this.focusElem = btn[0];
			btn.addClass('closeBoxBtnSelected');
		}		
		qZoneBoutton.append(btn);
		i++;
	}
	this.divContent.append(qZone);
};
QuestionBox.prototype.addContent=MessageBox.prototype.addContent;
QuestionBox.prototype.setValidationBtn=function(psLibelle, psListenerName, psControllerName){
	this.exitBtn = $("<input>", {
		'type':'submit',
		'value':psLibelle,
		'class':'closeBoxBtn'
	});
	var loThis=this;
	this.exitBtn.click(function(){
		Dispatcher.loadListener(psListenerName, psControllerName);
		loThis.hide();
	});
};
QuestionBox.prototype.show = function (){
	var divBtnFerme = $("<div>", {'class':'divBtnClose'});
	var divBas = $("<div>" ,{'class':'segmentBox divBas'});
	this.divCadre.append(divBas);
	
	if(this.nbQuestion > 1){		
		if(this.exitBtn!=null){
			var btnFermer = this.exitBtn;			
		}else{
			var loThis=this;
			var btnFermer = $("<input>", {
				'type':'submit',
				'value':'Fermer',
				'class':'closeBoxBtn'
			});			
			btnFermer.click(function(){
				loThis.hide();
			});
		}		
		divBtnFerme.html(btnFermer);		
	}else{
		this.bufferBtn[0].className='';
		divBtnFerme.html(this.bufferBtn);
//		this.firstQzone.append(this.bufferBtn);
	}
	this.divCadre.append(divBtnFerme);
	this.centrerBox();
	$("body").addClass("bodyHided");
	this.divHideBody.show();
	this.divCadre.show();
	this.focusElem.focus();
};
QuestionBox.prototype.hide=MessageBox.prototype.hide;
QuestionBox.prototype.centrerBox=MessageBox.prototype.centrerBox;
/**
 * Class LoadingBox.
 * Permet d'afficher une fenetre de chargement
 */
function LoadingBox(){	
	//Elements visuels
	this.divCadre = $("<div>" ,{'class':'messageBoxGlobal'});
	this.divHideBody = $("<div>" ,{'class':'messageBoxBody'});
	this.divTitle = $("<div>" ,{'class':'msgWarning messageBoxTitle'});
	this.divContent = $("<div>" ,{'class':'segmentBox messageBoxContent'});
	this.spinner = $('<img>', {'src':Utils.libPath+'files/images/loading.gif', 'alt':'Chargement...'});
	this.textInfo = $('<p>');
	
	//Initialisation des elements	
	this.divContent.append(this.textInfo);
	this.divContent.append(this.spinner);
	this.divCadre.hide();	
	this.divHideBody.hide();
	this.divCadre.html(this.divTitle);
	this.divCadre.append(this.divContent);
	$("body").append(this.divHideBody);	
	$("body").append(this.divCadre);
}
LoadingBox.prototype.setTextInfo=function(psMessage){
	this.textInfo.html(psMessage);
};
LoadingBox.prototype.show=function(psTitle, poConteneur){
	//On centre le message sur la zone à charger, sauf si elle est caché ou qu'il s'agit de MessageBox
	this.conteneur = poConteneur==undefined||poConteneur.css('display')=='none'||poConteneur.attr('class')=='segmentBox messageBoxContent'||$(poConteneur[0].parentElement).attr('class')=='segmentBox messageBoxContent'?$("body"):poConteneur;
	this.divTitle.html(psTitle);
	this.centrerBox();
	this.divHideBody.show();
	this.divCadre.show();
};
LoadingBox.prototype.hide = function (){
	$("body").removeClass("bodyHided");
	this.divHideBody.hide();
	this.divCadre.hide();
};
LoadingBox.prototype.centrerBox=function(){
	var xPos = parseFloat(this.conteneur.css('width')) / 2.0 - parseFloat(this.divCadre.css('width')) / 2.0;
	var yPos = parseFloat(this.conteneur.css('height')) / 2.2 - parseFloat(this.divCadre.css('height')) / 2.0;
	if(yPos < 0) yPos = 0;
	//centrage automatique
	this.divCadre.css({
		'top':yPos+'px',
		'left':xPos+'px'
	});
};

