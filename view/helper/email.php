<?php

Class Email extends AppHelper
{
	private $datas = array();
	
	public function __set($name,$value)
	{
		$this->datas[$name] = $value;
	}
	
	public function __get($name)
	{
		return $this->datas[$name];
	}
	
	
	public function send()
	{
		$destinataire='soulassol.nicolas@gmail.com';
		$email_expediteur='';
		$email_reply='email_de_reponse@fai.fr';
		
		if($this->datas['destinataire'])
		{
			$destinataire= $this->datas['destinataire'];
		}
		$sujet = $this->datas['sujet'];
		
		$message_html= '<style> a img {border:none;} </style>'.$this->datas['message']; 

		$headers = 'From:  <'.$email_expediteur.'>'."\n";
		$headers .= 'Return-Path: <'.$email_reply.'>'."\n";
		$headers .= 'MIME-Version: 1.0'."\n";
		$headers .= 'Content-Type: text/html; charset="UTF-8"'."\n";
		
		$footer = isset($this->datas['footer']) ? $this->datas['footer'] : '' ;
		
		$message = $message_html."\n\n".$footer;
	
		if(mail($destinataire,$sujet,$message,$headers))
		{
			return true;
		}
		else
		{
			return false;
		}
	
	}
}