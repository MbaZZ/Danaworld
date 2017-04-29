<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	
	<head>
		<?php
			header('Content-type: text/html; charset=UTF-8'); 
			$this->addCss('default');
			$html = $this->getHelper('html');
			echo $this->getHeaders();
			
		?>
	</head>
	
	<body>
	<div id ="global">
		<div id="header">
			<div id="logo"></div>
			<div id="search">
				<input type="text" style="-moz-border-radius-topright:0;-moz-border-radius-bottomleft:0;" size="30" value="Rechercher sur le site" />
				<input type="submit" value="Valider" />
			</div>
		</div>
		<div id="menu">
			<ul>
				<li><a href="jj">Test</a></li>";
			</ul>
		</div>
		<div id="sous_menu"></div>
		<div id="main">
			<div class="menu">
				<div class="headerbar"><a>Menu principal</a></div>
				<div class="content_menu">
					<ul class="submenu">
						<li><a href="">Accueil</a></li>
						<li><a href="">Actualités</a></li>
						<li><a href="">Nouveauté</a></li>
					</ul>
				</div>
				
				<div class="headerbar white"><a>Connexion</a></div>
				<div class="content_menu white" >
					<form action="<?php echo ROOT ?>utilisateurs/login" style="padding:10px;color:white" method="post">
						<label for="login">Identifiant</label>
						<input type="text" name="login" />
						<label for="login">Mot de passe</label>
						<input type="password" name="mdp" /><br/><br/>
						<input type="submit" value="Enregistrer" />
					</form>
				
				</div>
			</div>
		
			<div id="content">
				<?php 
					echo $this->getMessage();
					echo $this->getContent('content_url');
				?>
			</div>
		</div>	
	</div>
	</body>
</html>
