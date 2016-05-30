<?php

class appHelper
{
	protected  $layout;
	public function appHelper($layout)
	{
		$this->layout = $layout;
		$this->init($layout);

	}

	public function init($layout)
	{

	}
}
?>