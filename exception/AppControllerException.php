<?php
/** 
 * @author MbZ
 */

abstract class AppControllerException extends Exception{
    abstract static public function ControllerNotFoundException($name);
    abstract static public function ActionNotFoundException($name);
    abstract static public function MethodeNotFoundException($name);
}
?>