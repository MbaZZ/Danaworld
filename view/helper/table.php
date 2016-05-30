<?php

Class Table 
{
	private $nbResult;
	public function tableSort($name,$champ="")
	{
		$data = $_GET;
		$data['sort'] = 'ASC';
		$data['champ'] = $champ;
		if(isset($_GET['sort']) and $_GET['sort'] == $data['sort'])
		{
			$data['sort'] = 'DESC';
		}
		unset($data['url']);
		$newurl = $_SERVER['REDIRECT_URL'].'?'.http_build_query($data);
	
		return("<a href = $newurl>$name</a>");
	
	}
	
	public function getSort()
	{
		$order = array();
		if(isset($_GET['champ']))
		{
			$order = array($_GET['champ']=>$_GET['sort']);
		}
		return($order);
	}

	public function paginate($requete)
	{
		$this->nbResult = count($requete);
		if($_GET['page'])
		{
			$page = $_GET['page'];
			$page = $page +1;
			$limit = ($_GET['page'] * 100) -100;
			
			$requete = array_slice($requete, $limit, 100);
		}
		else
		{
			$requete = array_slice($requete, 0, 100);
		}
		return $requete;
	}
	public function pageNumbers()
	{
		$data = $_GET;
		unset($data['url']);
		if($this->nbResult > 100)
		{
			$nbPage = ($this->nbResult / 100);
			$nbPage +=1;
			for($i=1;$i<$nbPage;$i++)
			{
				
				$data['page'] = $i;
				$newurl = $_SERVER['REDIRECT_URL'].'?'.http_build_query($data);
				$pos .= "<a href = $newurl >$i</a>".' | ';
			}
		}
		else
		{
			$data['page'] = 1;
			$newurl = $_SERVER['REDIRECT_URL'].'?'.http_build_query($data);
			$pos .= "<a href = $newurl >1</a>";
		}
		return $pos;
	}
	
	
}