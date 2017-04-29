<?php

abstract class AppViewControllerException extends Exception{
    public function AppViewControllerException($message){
        parent::__construct($message, 0);
    }
    abstract static public function HelperNotFoundException($helper, $controller);
    abstract static public function ViewNotFoundException($name);
    abstract static public function LayoutNotFoundException($name);
}
?>