<?php
require_once 'utils/CSSmin.php';
require_once 'utils/JSMin.php';
class layoutManagerCache extends layoutManager{
	private $pjsFiles=array();
	private $jsFileName="f";
	private $pcssFiles=array();
	private $cssFileName="";
	private $isAjaxRequest=false;
	private $linkAlreadyLoaded;
	public function __construct($layoutName){
		parent::__construct($layoutName);
		$this->isAjaxRequest = isset($_POST['request']) && $_POST['request'] == 'ajax' || isset($_GET['request']) && $_GET['request'] == 'ajax';
		if($this->isAjaxRequest){
			$this->linkAlreadyLoaded = isset($_POST['linkLoaded'])?$_POST['linkLoaded']:$_GET['linkLoaded'];
		}else{
			$this->linkAlreadyLoaded = array('js' => array(), 'css' => array());
		}
		$this->cssFiles = $this->pcssFiles;
		$this->jsFiles = $this->pjsFiles;
	}
	public function addCss($file, $after=true){	
		$path = "files/css/".$file.".css";
		if(!file_exists("files/css/".$file.".css")){
			$path = router::getFramworkPath()."files/css/".$file.".css";
		}
		if($after){
			$this->cssFileName = md5($file.$this->cssFileName);
			array_unshift ($this->pcssFiles, $path);
		}else{
			$this->cssFileName = md5($this->cssFileName.$file);
			array_push ($this->pcssFiles, $path);
		}
	}
	public function addJs($file, $after=true){
		if(isset($this->pjsFiles[$file]))return;
		$filePath = "files/js/".$file.".js";
		if(!file_exists($filePath)){
			$filePath=router::getFramworkPath().$filePath;
			//Gestion des fichiers virtuels avec fichiersController si le fichier reel n'existe pas
			if(!file_exists($filePath)){ 
				$file = explode('?', $file); $file = $file[0];
				$this->addHeader("<script src='".ROOT."fichiers/js/".$file.".js' type='text/javascript'></script>",$after);
				return;
			}
		}
		if(!$after){
			$this->jsFileName = md5($file.$this->jsFileName);
			array_unshift ($this->pjsFiles, $filePath);
		}else{
			$this->jsFileName = md5($this->jsFileName.$file);
			array_push ($this->pjsFiles, $filePath);
		}
	}
	public function getHeaders(){
		if($this->isAjaxRequest){ //Pour ne pas recharger un meme script de fichier en req AJAX
			$this->pjsFiles = array_diff($this->pjsFiles, $this->linkAlreadyLoaded['js']);
			$this->pcssFiles = array_diff($this->pcssFiles, $this->linkAlreadyLoaded['css']);
		}
		$file='cache/'.str_replace('/', '_', $this->jsFileName);
		$r=substr(md5($file),5,5);
		$fileName=router::getFramworkPath().$file;
		if(count($this->pjsFiles) > 0){
			if(!file_exists($fileName.'.js')){
				$jsFile = fopen($fileName.'.js', 'c+');
				foreach($this->pjsFiles as $f) fwrite($jsFile, JSMin::minify(file_get_contents($f))."\n");
				fclose($jsFile);
			}
			$_SESSION['content.fichierSrc.allJS'.$r] = $file.'.js';
			$this->addHeader("<script src='".ROOT."fichiers/js/allJS".$r.".js' type='text/javascript'></script>", false);
		}
		$file='cache/'.str_replace('/', '_', $this->cssFileName);
		$r=substr(md5($file),5,5);
		$fileName=router::getFramworkPath().$file;
		if(count($this->pcssFiles) > 0){
			if(!file_exists($fileName.'.css')){
				$minCss = new CSSmin();
				$cssFile = fopen($fileName.'.css', 'c+');
// 				foreach($this->pcssFiles as $f) fwrite($cssFile, $minCss->run(file_get_contents($f))."\n");
				foreach($this->pcssFiles as $f){
					$path = dirname(dirname(str_replace(router::getFramworkPath(), LIB, $f)));
					Logger::tracerErreur('path : ' . $path. ' f '.$f);
// 					fwrite($cssFile, $minCss->run(str_replace('../fonts/', '../../../danaworld/files/css/fonts/', str_replace('../img', '../../'.$path.'/img', str_replace('../images/', $path.'/images/', file_get_contents($f)))))."\n");
					fwrite($cssFile, $minCss->run(str_replace('../fonts/', '../../../../../'.$path.'/fonts/', str_replace('../img', '../../'.$path.'/img', str_replace('../images/', $path.'/images/', file_get_contents($f)))))."\n");
// 					fwrite($cssFile, $minCss->run(str_replace('../', '../../'.$path.'/', file_get_contents($f)))."\n");
				}
				fclose($cssFile);
			}
			$_SESSION['content.fichierSrc.allCSS'.$r] = $file.'.css';
			$this->addHeader("<link href='".ROOT."fichiers/css/allCSS".$r.".css' title='design' type='text/css' media='all' rel='stylesheet' />", false);
		}
		$this->cssFiles = $this->pcssFiles;
		$this->jsFiles = $this->pjsFiles;
		return parent::getHeaders();
	}
}
?>