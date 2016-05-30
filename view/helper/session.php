<?php
require_once "view/helper/appHelper.php";
class Session extends appHelper
{
	
        public function __construct()
        {
                if(session_id() === "")
                {
					session_start();
				
                }
        }
        public function __set($name,$value)
        {
                $_SESSION["Variables"][$name] = $value;
        }
        public function &__get($name)
        {
			return $_SESSION["Variables"][$name];
		}
		
		public function destroy()
		{
			session_destroy(); 
		}
      
} 
