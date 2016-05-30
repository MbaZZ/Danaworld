<?php
require_once "exception/AppLibraryNotFoundException.php";
class LibraryNotFoundException extends AppLibraryNotFoundException{
    public static function xmlConfigNotFound() {
        return new LibraryNotFoundException("Impossible de trouver le fichier de configuration (router()) ");
    }
}
?>