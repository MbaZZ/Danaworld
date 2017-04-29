<?php
class fichiers_Controller extends abstractController{
 	//public static $interceptors = array("jquery");
	
	private $basePath = "files";
	
	public function init($html){
		$html->setLayoutName('contenuSeulement.php');
	}
	public function css($html, $params){
		header('Content-Type:text/css');	
		$params = explode('.',$params['id']);
		$urlFile=$params[0];
		if(isset($_SESSION['content.fichierSrc.'.$urlFile])){
			include $_SESSION['content.fichierSrc.'.$urlFile];
			unset($_SESSION['content.fichierSrc.'.$urlFile]);
			return "noView";
		}else{//Sinon on vérifie si ce n'est pas un contenu généré
			return "noView";
		}
	}	
/*	public function js($html, $params){
		$urlFile = $params['id'];
		header('Content-Type:application/javascript');		
		if(isset($_SESSION['content.fichier.'.$urlFile])){
			if(isset($_SESSION['content.fichier.launcher.js.launcher'])){
				$endl = "\n\n";
				echo 'function launcher(){};'.$endl;
				foreach ($_SESSION['content.fichier.launcher.js.launcher'] as $varName => $value){
					echo 'launcher.'.$varName.'='.$value.';';
					echo 'if(Utils.set'.ucfirst($varName).')Utils.set'.ucfirst($varName).'(launcher.'.$varName.')'.$endl;
				}
			}
			echo $_SESSION['content.fichier.'.$urlFile];
		}else{
			$existingFile = $this->existingFile($this->basePath."/js/".$urlFile);
			if($existingFile!=false){ //Si ce fichier existe, on l'utilise
				return $existingFile; 
			}else{//Sinon on vérifie si ce n'est pas un contenu généré
				return "noView";
			}
		}
		return "noView";
	}
	*/
	public function test($html, $params){
		Logger::tracerErreur('test');
		echo "Ceci est un test";
		return "noView";
	}
	
	public function js($html, $params){
		$params = explode('.',$params['id']);
		$urlFile=$params[0];
		header('Content-Type:application/json');
		if(isset($_SESSION['content.fichier.'.$urlFile])){
			if(is_array($_SESSION['content.fichier.'.$urlFile])){
				//Nvlee version
				$jsonData = json_encode($_SESSION['content.fichier.'.$urlFile]);
				if($params[1] == 'js')
					$jsonData = '$(document).ready(function(){ Dispatcher.getInstance().loadJsControllerDatas('.$jsonData.');});';
				
			}else{
				$jsonData = $_SESSION['content.fichier.'.$urlFile];
			}
			$_SESSION['content.fichier.'.$urlFile] = ''; //Suppression du contenu une fois celui ci recupéré
			echo $jsonData;
		}elseif(isset($_SESSION['content.fichierSrc.'.$urlFile])){
			include $_SESSION['content.fichierSrc.'.$urlFile];
			$_SESSION['content.fichierSrc.'.$urlFile]='';
			unset($_SESSION['content.fichierSrc.'.$urlFile]);
			return "noView";
		}else{
			$existingFile = $this->existingFile($this->basePath."/js/".$urlFile);
			if($existingFile!=false){ //Si ce fichier existe, on l'utilise
				return $existingFile; 
			}else{//Sinon on vérifie si ce n'est pas un contenu généré
				return "noView";
			}
		}
		
		return "noView";
	}
	public function existingFile($filePath){
		if(!file_exists($filePath)){
			return false;			
		}	
		return "../".$filePath;
	}
	public function img($html, $params){
		
		Logger::tracerErreur('chage ton image '.$this->basePath."/img/".$params['id']);
		return $this->images($html, $params, $this->basePath."/img/".$params['id']);
	}
	public function images($html, $params, $filePath = NULL){
		$file_extention = explode('.',$params['id']);
		$extention = $file_extention[1];
		
		if($filePath == NULL)
			$filePath = $this->basePath."/images/".$params['id'];
		
		if(!file_exists($filePath))
			$filePath = Router::getFramworkPath().$this->basePath."/images/".$params['id'];
		
		if(!file_exists($filePath))
			Logger::tracerErreur("Fichier $filePath introuvable");
		
		
		switch($extention){
			case "png":
			header("Content-type: image/png"); 			
			$im = imagecreatefrompng($filePath); 			
			break;
			
			case "jpg":case"jpeg":
				header('Content-Type:image/jpeg');
				$im = imagecreatefromjpeg($filePath);				
			break;
			
			case "gif":
				header('Content-Type:image/gif');
				$im = imagecreatefromgif($filePath);				
			break;				
		}
	
		//Recuperation des paramètres optionels
		if(isset($_GET['size_x']) || isset($_GET['size_y'])){
			$conserveRation = true;	
			if(isset($_GET['resizeType'])){
				switch($_GET['resizeType']){
					case 'conserverRatio':
						$conserveRation = false;
				}
			} 			
			if(isset($_GET['size_x'])){
				$sizeX = $_GET['size_x'];
			}else{
				$sizeX = 9999;
			}
			if(isset($_GET['size_y'])){
				$sizeY = $_GET['size_y'];
			}else{
				$sizeY = 9999;
			}				
			$imgresize = $this->resizeImg($im, $sizeX, $sizeY, $conserveRation);
			if($imgresize == null){
				 imagedestroy($imgresize);
				 throw new ControllerException("erreur lors du resize de ".$params['id']);
			}
			imagedestroy($im);
			$im = $imgresize;
		}				
		
		imagepng($im);		
		imagedestroy($im);
		return "noView";
	}
	private function resizeImg($img, $size_x, $size_y, $consereRatio = false) {	
		//compute resize ratio
	    $hratio = $size_y / imagesy($img);
	    $wratio = $size_x / imagesx($img);
	    $ratio = min($hratio, $wratio);
	
	    //if the source is smaller than the thumbnail size, 
	    //don't resize -- add a margin instead
	    //(that is, dont magnify images)
//	    if($ratio > 1.0)
//	        $ratio = 1.0;
	
	    //compute sizes
	    $sy = floor(imagesy($img) * $ratio);
	    $sx = floor(imagesx($img) * $ratio);
	    
	    if($consereRatio){
	    	$size_x = $sx;
	    	$size_y = $sy;
	    	$m_y = 0;
	    	$m_x = 0;
	    }else{		
		    //compute margins
		    //Using these margins centers the image in the thumbnail.
		    //If you always want the image to the top left, 
		    //set both of these to 0
		    $m_y = floor(($size_y - $sy) / 2);
		    $m_x = floor(($size_x - $sx) / 2);
	    }		
		//create the image, of the required size
	    $new = imagecreatetruecolor($size_x, $size_y);
	    if($new === false) {
	        //creation failed -- probably not enough memory       
	    	return null;
	    }
	
		if(!$consereRatio){
		    //Fill the image with a light grey color
		    //(this will be visible in the padding around the image,
		    //if the aspect ratios of the image and the thumbnail do not match)
		    //Replace this with any color you want, or comment it out for black.
		    //I used grey for testing =)
		    $fill = imagecolorallocate($new, 0, 0, 0);
		    imagefill($new, 0, 0, $fill);
		}
	   
	
	    //Copy the image data, and resample
	    //
	    //If you want a fast and ugly thumbnail,
	    //replace imagecopyresampled with imagecopyresized
	    if(!imagecopyresampled($new, $img,
	        $m_x, $m_y, //dest x, y (margins)
	        0, 0, //src x, y (0,0 means top left)
	        $sx, $sy,//dest w, h (resample to this size (computed above)
	        imagesx($img), imagesy($img)) //src w, h (the full size of the original)
	    ) {
	        //copy failed
	        imagedestroy($new);
	        return null;
	    }
	    //copy successful
	    return $new;
	}
}
?>