<?php
class timer extends appHelper{
	public function convertSecondesToTime($piSecondes){
		$min = ($piSecondes/60);
		return $this->get2digits($min/60).":" . $this->get2digits($min%60) . ":" . $this->get2digits($piSecondes%60);
	}
	public function get2digits($piNumber){
		$piNumber = (int) $piNumber;
		if($piNumber<10)$piNumber='0'.$piNumber;
		return $piNumber;
	}
}