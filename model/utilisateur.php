<?php 
require_once "model/bddModel.php";
require_once "service/validateUtil.php";

class utilisateur extends bddModel 
{
	protected $table = "utilisateurs"; 
	public $validates = array(
		'login'=>'L\'dentifiant est obligatoire',
		'mdp'=>'Mot de passe obligatoire'
	);
	public function isValid(){
		$sql = $this->query('select id,login from utilisateurs where login = "'.$this->login.'" and mdp = "'.sha1($this->mdp).'"');
	
		if(!$sql OR sizeof($sql) <> 1){ //après requete table utilisateur
				return false;
			}
			else{
				$_SESSION[ROOT]['auth'] = $sql[0];
				/*if($_SESSION[ROOT]['auth']['id']){
					//throw new ControllerException("Erreur d'authentification : l'utilisateur n'a pas d'id");
					return false;
				}*/
				return true;
			}
	}
	
	private function md5($mdp){
		if($mdp){
			$mdp = md5($mdp.'cette chaine est une chaine anti pirate');
			return($mdp);
		}
	}
	public function isConnected(){
		if(isset($_SESSION[ROOT]['auth'])){
			return true;
		}
	}
	public function logout(){
		unset($_SESSION[ROOT]['auth']);
	}
	public function getAuth(){
		return $_SESSION[ROOT]['auth'];
	}
}
?>

