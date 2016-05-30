<?php

class jquery_interceptor extends appInterceptor {
    /**
     *
     * @param ViewController $html
     * @return String viewName or NUll
     */
    public function beforeController($html){ 
     	$html->addJs("danaworld.utils",false);
        $html->addJs("jquery",false);
    	$html->addCss('messageBox');
		$html->addJs('messageBox');

		//Bootstrat
		$html->addJs('bootstrap/bootstrap');
		$html->addCss('bootstrap/bootstrap');
// 		$html->addJs('bootstrap/bootstrap-select');		
		
		$this->addHelper('html');
        if(isset($_POST['request']) || isset($_GET['request'])){
        	if(isset($_GET['title']) && $_GET['title']!='')
        		$html->setTitle($_GET['title']);
			if( isset($_POST['request']) && $_POST['request'] == "ajax" || isset($_GET['request']) && $_GET['request'] == "ajax")
        		$html->setLayoutName("emptyLayout.php");			
		}  
    	return NULL;
    }

    public function afterController($html){
		//$html->debug = "";		
		if(isset($_GET['curHeads'])){
			//On tri les en-têtes pour n'envoyer que la différence 
			$html->removeHeaders(explode("|-|", $_GET['curHeads']));			
			//$html->debug = "<p>". $_GET['curHeads']."</p>";
		}
        return NULL;
    }
}
?>
