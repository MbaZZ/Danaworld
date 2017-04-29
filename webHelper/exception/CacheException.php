<?php

/**
 * Description of ControllerException
 *
 * @author MbZ
 */
class CacheException extends Exception{
    public function ControllerException($message){
        parent::__construct($message, 0);
    }
}
?>