<?php

Class Files
{
	private $files = array();

	public function __set($name,$value)
	{
		$this->files[$name] = $value;
	}
	
	public function __get($name)
	{
		return $this->files[$name];
	}
	
	public function uploadFile()
	{
		$name = '';
		if(isset($this->nameFile))
		{
			$name = $this->nameFile;
		}
		$content_dir = $this->uploadDir ? $this->uploadDir  : 'files/images/public/';
		$name_file = $name ? $name  : $_FILES['file']['name'];
		$tmp_file = $_FILES['file']['tmp_name'];
		
		if(!is_uploaded_file($tmp_file) )
		{
			exit("Le fichier est introuvable");
		}
		
		if(!move_uploaded_file($tmp_file, $content_dir.$name_file) )
		{
			exit("Impossible de copier le fichier dans $content_dir");
		}
	}

	public function deleteFile($fileName)
	{
		$name = '';
		if(isset($this->nameFile))
		{
			$name = $this->nameFile;
		}
		$content_dir = $this->uploadDir ? $this->uploadDir  : 'files/images/public/';
		unlink($content_dir.$fileName);
		
	}
	public function listeFiles($listedir = null)
	{
		
		if(!$listedir)
		{
			$listedir = 'files/images/public/';
		}
		
		$dir = opendir($listedir); 
		while($file = readdir($dir)) 
		{
			if($file != '.' && $file != '..' && !is_dir(ROOT.$listedir.'/'.$file))
			{
				$img[] = array('link'=>ROOT.$listedir.$file,'name'=>$file);
			}
		}
		return $img;
		closedir($dir);
	}
}

?>