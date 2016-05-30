<?php

class Mysql
{
	public function do_config() {
		if (class_exists('db_config'))
		{
			$this->config = new db_config();
		}
	}

	public function Mysql($base)
	{
		$this->do_config();
		$base = isset($base) ? $base : "default";
		if(isset($this->config->$base))
		{
			foreach($this->config->$base as $param => $value)
			{
				$this->$param = $value;
			}
			try
			{
				$this->Lien = new PDO('mysql:host='.$this->Serveur.';dbname='.$this->Bdd, $this->Identifiant,$this->Mdp);
				$this->Lien->query("SET NAMES 'utf8'");
			}

			catch(Exception $e)
			{
				echo 'Erreur de connexion à la base de données !!<br />';
					
			}
				
		}
	}

	public function TabResSQL($requete)
	{
		$i = 0;
		$TabResultat=array();
		$resultats = $this->Lien->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
		$resultats->setFetchMode(PDO::FETCH_OBJ); // on dit qu'on veut que le r�sultat soit r�cup�rable sous forme d'objet
		while( $ligne = $resultats->fetch() ) // on r�cup�re la liste des membres
		{
			foreach ($ligne as $clef => $valeur) $TabResultat[$i][$clef] = $valeur;
			$i++;
		}
		$resultats->closeCursor(); // on ferme le curseur des r�sultats
		return $TabResultat;

	}

	function ExeSql($requete)
	{

		try
		{
			$query = $this->Lien->exec($requete) ;
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage(), "\n");
		}
		return $query;
	}

	public function lastId()
	{
		return $this->Lien->lastInsertId();
	}
}
?>