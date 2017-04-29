<?php 

class validate_util{
	
	private $validates = array();
	private static $errors = array();
	
	public function isValide($modelObj){
		$formPost = $modelObj->getData();
		$value = true;
		foreach($formPost as $post => $mess){
			if(isset($modelObj->validates[$post])){
				if(is_array($modelObj->validates[$post]) ){ // si il y a un array double on appelle la règle de validation par exemple validMail
					$rules = $modelObj->validates[$post][0];
					if($message = validate_util::$rules($modelObj->$post)){ // test de la règle de validation
						validate_util::setMessage($post,$message);
						$value = false;
					}
					
				}
				elseif(!$modelObj->$post || str_replace(' ','', $modelObj->$post) == ""){ // cas ou il n'y a pas de regle de validation et que le champ spécifié est vide
					validate_util::setMessage($post,$modelObj->validates[$post]);
					$value = false;
					Logger::tracerErreur('Validation error');
				}
			}
		}
		return $value;
	}
	public static function setMessage($name,$error){
		validate_util::$errors[$name] = $error;
	}
	public static function getMessage($name){
		if(isset(validate_util::$errors[$name])){
			return validate_util::$errors[$name];
		}
	}
	
	static function validMail($val){
		$verifmail="!^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]{2,}\.[a-zA-Z]{2,4}$!";
			if(!preg_match($verifmail,$val)) {
				return ('Merci de saisir une adresse correct');
			
		}
	}
	
}
?>
