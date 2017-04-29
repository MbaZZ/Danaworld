<?php header('Content-type: text/html; charset=UTF-8'); ?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
      <title><?php echo $this->title; ?></title>
      <?php echo $this->getHeaders(); ?>
   </head>
    <body>	
	<div id="content">
        <?php
         $this->getContent("content_url");
        ?>
	</div>
    </body>
</html>
