<?php

class utilisateurs_controller extends appController {
		
	protected $helpers = array('html','form','session','files');
	protected $models = array('utilisateur');
	public function init($html){
		Logger::tracerErreur('ici');
		$this->layoutObj->setLayoutName('sessionSecurisee.html');		
	}
	public function login(){		
		$this->layoutObj->login_message = "";
		$this->formHelper->setModel($this->utilisateurModel);
		$data = $this->formHelper->getValidForm();		
		Logger::tracerInfo("Login en cours...");
		if($data){
			if($origine = $_SESSION[ROOT]['page_origine']){
				$this->htmlHelper->redirect($origine,'ext');
			}
			else{
				$this->htmlHelper->redirect('');
			}
		}else{
			$this->formHelper->setMessage("auth", "Login et/ou mot de passe incorrecte !");
			Logger::tracerInfo("Login échoué.");
		}
		
	}
	
	public function logout(){
		$this->utilisateurModel->logout();
		$this->htmlHelper->redirect('./');
		return NULL;
	}
	
	
}
?>