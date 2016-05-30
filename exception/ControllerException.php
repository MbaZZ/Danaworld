<?php
require_once "exception/AppControllerException.php";
class ControllerException extends AppControllerException{

    static public function ControllerNotFoundException($name){
    	return new ControllerException ("Le contrôleur \"<b>".$name."</b>\" est introuvable !");
    } 
    static public function ActionNotFoundException($name){
    	return new ControllerException ("L'action ".$name." n'existe pas !");
    }
    static public function MethodeNotFoundException($name){
    	return new ControllerException ("La méthode ".$name." n'existe pas !");
    }
}

?>