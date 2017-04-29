<?php
class html
{
	private $valide;
	private $name;
	
	public function charset($type)
	{
		header('Content-Type: text/html;charset='.$type);
		
	}
	public function link($name,$conditions = array())
	{
		$chaine = "";
		$href= "";
		$onclick = 'onclick="';
		$libel=$name;
		if(isset($conditions['libel'])){
			$libel = $conditions['libel'];
			unset($conditions['libel']);
		}
		
		foreach($conditions as $key => $val)
		{
			if($key == 'href'){
				$val = ROOT.$val;
				$href=$val;
				$chaine .= $key.'="'.$val.'" ';
			}
			else if($key == 'onclick'){ //concatenation 
					$onclick .= $val;
			}			
			else {
				$chaine .= $key.'="'.$val.'" ';
			}			
		}		
		$onclick.='"';
		$res = "<a $onclick $chaine ><span>$libel</span></a>";
		return($res);
	}
	
	public function image($src,$info = "")
	{
		$filePath = "files/img/".$src;
		$imgSrc = ROOT.$filePath;
		if(!file_exists($filePath)){
			$imgSrc = LIB.$filePath;
			//Gestion des fichiers virtuels avec fichiersController si le fichier reel n'existe pas
			if(!file_exists(router::getFramworkPath().$filePath)){ 
				$filePath = explode('?', $filePath); $filePath = $filePath[0];
				$imgSrc = ROOT."fichiers/images/".$src;
			}
		}
		return "<img src='".$imgSrc."' $info />";
	}
	public function message($mess)
	{
		return($mess);
	}
	
	public function redirect($lien,$type = null)
	{
		$src = ROOT.$lien;
		if($lien == 'referer')
		{
			$src = $_SERVER['HTTP_REFERER'];
		}
		if($type == 'ext')
		{
			$src = $lien;
		}
	
		header("Location: ".$src);exit();
	}
}

