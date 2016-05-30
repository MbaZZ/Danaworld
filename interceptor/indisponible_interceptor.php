<?php
class indisponible_interceptor extends appInterceptor {
    /**
     *
     * @param ViewController $html
     * @return String viewName or NUll
     */
    public function beforeController($html){    
    	
    	/*$this->addModel("utilisateur");
		if($this->utilisateurModel->isConnected()){
			return NULL; //Ne concerne pas les utilisateur authentifie ?
		}
       	
    	if(isset($_GET['admin']) && $_GET['admin'] == "dev"){//Indentification
			$this->addHelper("html");
			$this->addHelper("session");
    		$this->sessionHelper->error = 'Vous n\'avez pas accès à cette page';
			return $this->loadControllerAction('utilisateurs','login');
			die('<b><h1>ERREUR 403</h1></b>Vous n\'avez pas accès à cette page');
    	}*/
    	
    	//Page cliente
		$html->setTitle("Oops ressource indisponible");
		$html->addCss("indisponible");
	 	return "messages/indisponible.html";     	
    }
   /*  public function afterController($html){
     	return $this->beforeController($html);
     }*/
}
?>
