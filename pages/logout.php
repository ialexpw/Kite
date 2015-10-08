<?php
	/*
		Kite URL Shortener
		Version 1.0.0
		iAlex (http://codecanyon.net/iAlex)
		logout.php
	*/
	
	$kt = new Kite();
	global $Site;
	
	session_destroy();
	
	$kt->Go($Site[0]['website_address'] . 'p/login');
?>