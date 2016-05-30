<?php
/**
 * Description of viewControllerException
 *
 * @author MbZ
 */
 require_once "exception/AppViewControllerException.php";

class ViewControllerException extends AppViewControllerException{

    public static function HelperNotFoundException($helper, $controller) {
         return new ViewControllerException("Le helper \"<b>".$helper."</b>\" est introuvable !");
    }
    public static function LayoutNotFoundException($name) {
         return new ViewControllerException("Le layout \"<b>".$name."</b>\" est introuvable !");
    }
    public static function ViewNotFoundException($name) {
        return new ViewControllerException ("La vue est introuvable !".$name);
    }
}
?>