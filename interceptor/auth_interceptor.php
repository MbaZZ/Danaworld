<?php
class auth_interceptor extends appInterceptor{

	protected function init($html){
		$this->addHelper("html");
		$this->addHelper("session");
		if($_SERVER['REQUEST_URI'] <> ROOT.'utilisateurs/login'){	
			$_SESSION[ROOT]['page_origine'] = $_SERVER['REQUEST_URI'];
		}
	}
	
	public function beforeController($layout){
		$this->addModel("utilisateur");
		if(!$this->utilisateurModel->isConnected()){
			$this->sessionHelper->error = 'Vous n\'avez pas accès à cette page';
			return $this->loadControllerAction('utilisateurs','login');
			die('<b><h1>ERREUR 403</h1></b>Vous n\'avez pas accès à cette page');
		}
		
	}
	
}

?>
