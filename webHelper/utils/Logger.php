<?php
class Logger{
	//Mettre tous les sites du framwork sous traces, sauf le logger
	private static $toutMettreSousTraces = true;
	
	private static $instance = NULL;

	private static function getInstance(){
		if(Logger::$instance == NULL){
			$configXml = router::getInstance()->getConfig();
			if(	(	strrpos($_SERVER['REQUEST_URI'], 'logger/') == false
					&& strrpos($_SERVER['REQUEST_URI'], 'launcher') == false
				)&& ($configXml != NULL 
					&& $configXml->projectMod != "" 
					&& $configXml->projectMod == "dev"
					&& $configXml->tracingLevel != "" 
					&& $configXml->tracingLevel != "0"
					|| Logger::$toutMettreSousTraces == true
				)
			)						
				Logger::$instance = new TextWriter();		
			else
				Logger::$instance = new emptyWriter();
		}
		return Logger::$instance;
	}
	private static $lastDeb = "";
	private static function tracerLastDeb($indent = +1){
		if(Logger::$lastDeb != ""){
			$libel = $indent==+1?"Deb ":" ";
			Logger::getInstance()->write(Logger::gras($libel. " ").Logger::$lastDeb, $indent);
			Logger::$lastDeb = "";
			return true;
		}
		return false;
	}
	private static function tracer($sText, $iIndentation = 0){
		if($iIndentation != -1)
			Logger::tracerLastDeb();
		Logger::getInstance()->write($sText, $iIndentation);
	}
	private static function tracerDebut($sType, $sName, $sInfo = ""){
		if($sInfo!= "")$sInfo=Logger::comment('//'.$sInfo);
		//Logger::tracer(Logger::gras($sType)." ".Logger::souligne($sName).$sInfo, +1);	
		Logger::tracerLastDeb();
		Logger::$lastDeb = Logger::gras($sType)." ".Logger::souligne($sName).$sInfo;
	}
	private static function tracerFin($sType, $sName, $sInfo = "", $sRetour = NULL){
		if($sInfo!= "")$sInfo="//".Logger::comment($sInfo);
		$sRetour = $sRetour!=NULL?"->".$sRetour:"";
		if(!Logger::tracerLastDeb(0))
			Logger::tracer(Logger::gras("Fin ".$sType)." ".Logger::souligne($sName).$sRetour.$sInfo, -1);	
	}
		
	public static function tracerDebutFonction($sText, $sInfo = ""){	
		Logger::tracerDebut("", $sText, $sInfo);
	}
	
	public static function tracerFinFonction($sText, $sInfo = "", $sRetour = NULL){	
		Logger::tracerFin("", $sText,$sInfo, $sRetour);
	}
	
	public static function tracerDebutController($controller_name){
		Logger::tracerDebut("contrôleur", $controller_name);
	}
	
	public static function tracerFinController($controller_name, $sRetour = NULL){
		if(is_array($sRetour))$sRetour='Array('.$sRetour=implode(', ', $sRetour).')';
		Logger::tracerFin("contrôleur", $controller_name,"",$sRetour);
	}
	
	public static function tracerDebutAction($action_name, $sInfo = ""){
		Logger::tracerDebut("Action", $action_name, $sInfo);
	}
	public static function tracerFinAction($action_name, $sRetour = "NULL"){
		if(is_array($sRetour))$sRetour='Array('.$sRetour=implode(', ', $sRetour).')';
		Logger::tracerFin("Action", $action_name,"",$sRetour);
	}
	
	public static function tracerDebutInterception($interceptor_name, $controller_class_name){
		Logger::tracerDebutFonction($interceptor_name,  "Intercepteur de " .$controller_class_name);
	}
		
	public static function tracerFinInterception($interceptor_name, $controller_class_name, $sRetour = "NULL"){
		if(is_array($sRetour))$sRetour='Array('.$sRetour=implode(', ', $sRetour).')';
		Logger::tracerFinFonction($interceptor_name, "Intercepteur de " .$controller_class_name ,$sRetour);
	}
	
	public static function tracerWarning($sText){
		Logger::tracer(Logger::gras("Exception")." : ".Logger::warningStyle($sText));
	}
	
	public static function tracerErreur($sText){
		Logger::tracer(Logger::gras("Exception")." : ".Logger::errorStyle($sText));
	}
		
	public static function tracerdebug($sText){
		Logger::tracer(Logger::gras("Debug")." : ".$sText);
	}
	
	public static function tracerException($eException){
		Logger::tracer(Logger::gras("Exception")." : ".Logger::errorStyle($eException->getMessage()));
	}
	
	public static function tracerInfo($sText){
		Logger::tracer(Logger::gras("Info")." : ".$sText);
	}
	
	
	/**
	 * Mise en forme generic
	 */
	/** Methode de mise en forme */
	private static function gras($sText){
		return Logger::getInstance()->gras($sText);
	}
	private static function souligne($sText){
		return Logger::getInstance()->souligne($sText);
	}
	private static function comment($sText){
		return Logger::getInstance()->comment($sText);
	}
	private static function errorStyle($sText){
		return Logger::getInstance()->errorStyle($sText);
	}
	private static function warningStyle($sText){
		return Logger::getInstance()->warningStyle($sText);
	}
	/** FIN Methode de mise en forme */
}

class webLogWriter{	
	private $file;
	private $sIndent = "";
	private $stringData = "";
	private $bHasError = true; //Seul les connexions avec erreur sont loggé
	public function indenter(){
		$this->sIndent .= ".&nbsp;&nbsp;&nbsp;";
	}
	public function desindenter(){
		$this->sIndent = substr($this->sIndent, 0, -19);
	}
	
	public function webLogWriter(){
		$filePath = router::getFramworkPath().'data/errlog.txt';
		if(filesize($filePath) >= 200000){
			//Vidage du fichier si sa taille dépasse 2 Mo
			file_put_contents($filePath, " ");
		}
			
		$this->file = fopen($filePath,'a');
		
// 		fwrite($this->file, "\n<br />-------------------------------".$_SERVER['REMOTE_ADDR']."->".$_SERVER['REQUEST_URI']."-----------------------------------<br />\n");
		$this->stringData .= "\n<br />-------------------------------".$_SERVER['REMOTE_ADDR']."->".$_SERVER['REQUEST_URI']."-----------------------------------<br />\n";
		if(isset($_POST) && count($_POST) > 0){
			$this->stringData .= "\n<br />-----------------------------------------------------------------------------------------------------<br />\n";
			$this->stringData .= 'POST DATA : '.print_r($_POST, true);
			$this->stringData .= "\n<br />-----------------------------------------------------------------------------------------------------<br />\n";
		}
		if(isset($_POST['request']) && $_POST['request'] == 'ajax' || isset($_GET['request']) && $_GET['request'] == 'ajax'){
			$this->stringData .= 'Ajax request';
			$this->stringData .= "\n<br />";
		}
	}
	public function write($sText, $iIndentation){
		if($iIndentation<0){
			$this->desindenter();
		}
// 		fwrite($this->file, "<u style='color:green;'>".date("Y-m-d H:i:s"). "</u> " .$this->sIndent.$sText."<br />");
		$this->stringData .= "<u style='color:green;'>".date("Y-m-d H:i:s"). "</u> " .$this->sIndent.$sText."<br />";
		if($iIndentation > 0){
			$this->indenter();
		}
	}
	/** Methode de mise en forme */
	public function gras($sText){
		return "<b>".$sText."</b>";
	}
	public function souligne($sText){
		return "<u>".$sText."</u>";
	}
	public function comment($sText){
		return "<span style='color:green;font-size:12px;'>".$sText."</span>";
	}
	public function errorStyle($sText){
		$this->bHasError = true;
		return "<span style='color:red;font-size:15px;'>".$sText."</span>";
	}
	public function warningStyle($sText){
		$this->bHasError = true;
		return "<span style='color:orange;font-size:12px;'>".$sText."</span>";
	}
	public function __destruct(){
		if($this->bHasError){
			fwrite($this->file, $this->stringData);
		}
	}
	/** FIN Methode de mise en forme */
}

class TextWriter{
	private $file;
	private $sIndent = "";
	private $stringData = "";
	public function TextWriter(){
		$filePath = router::getFramworkPath().'data/traces.log';
		if(filesize($filePath) >= 1000000){
			//Vidage du fichier si sa taille dépasse 10 Mo
			file_put_contents($filePath, " ");
		}
			
		$this->file = fopen($filePath,'a');
		
		// 		fwrite($this->file, "\n<br />-------------------------------".$_SERVER['REMOTE_ADDR']."->".$_SERVER['REQUEST_URI']."-----------------------------------<br />\n");
		$this->stringData .= "\n-------------------------------".$_SERVER['REMOTE_ADDR']."->".$_SERVER['REQUEST_URI']."-----------------------------------\n";
		// 		if(isset($_POST) && count($_POST) > 0){
		// 			$this->stringData .= "\n<br />-----------------------------------------------------------------------------------------------------<br />\n";
		// 			$this->stringData .= 'POST DATA : '.print_r($_POST, true);
		// 			$this->stringData .= "\n<br />-----------------------------------------------------------------------------------------------------<br />\n";
		// 		}
	}
	public function indenter(){
		$this->sIndent .= "  ";
	}
	public function desindenter(){
		$this->sIndent = substr($this->sIndent, 0, -2);
	}
	public function webLogWriter(){
	}
	public function write($sText, $iIndentation){
		if($iIndentation<0){
			$this->desindenter();
		}
// 		fwrite($this->file, "<u style='color:green;'>".date("Y-m-d H:i:s"). "</u> " .$this->sIndent.$sText."<br />");
		$this->stringData .= date("Y-m-d H:i:s"). ": " .$this->sIndent.$sText."\n";
		if($iIndentation > 0){
			$this->indenter();
		}
	}
	public function __destruct(){
		fwrite($this->file, $this->stringData);
	}
	/** Methode de mise en forme */
	public function gras($sText){
		return $sText;
	}
	public function souligne($sText){
		return $sText;
	}
	public function comment($sText){
		return $sText;
	}
	public function errorStyle($sText){
		return $sText;
	}
	public function warningStyle($sText){
		return $sText;	
	}
	/** FIN Methode de mise en forme */
}
/**
 * Utiliser pour ne pas ecrire de log 
 * @author alexis
 *
 */
class emptyWriter{
	public function indenter(){		
	}
	public function desindenter(){
	}
	public function webLogWriter(){
	}
	public function write($sText, $iIndentation){
	}
	/** Methode de mise en forme */
	public function gras($sText){
	}
	public function souligne($sText){
	}
	public function comment($sText){		
	}
	public function errorStyle($sText){
	}
	public function warningStyle($sText){
		
	}
	/** FIN Methode de mise en forme */
}

?>
