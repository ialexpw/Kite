<?php
	/*
		Kite URL Shortener
		Version 1.0.0
		iAlex (http://codecanyon.net/iAlex)
		config.php
	*/

	# Only used for debugging
	//ini_set('display_errors', 1);
	//error_reporting(E_ALL);

	include $_SERVER['DOCUMENT_ROOT'] . '/resources/library/Kite.class.php';
	include $_SERVER['DOCUMENT_ROOT'] . '/resources/library/PseudoCrypt.class.php';

	# Init the class
	$kt = new Kite();
	$ktSQL = new Kite_SQL();

	# Database details
	define('HOST', 'localhost');
	define('DBSE', 'database');
	define('USER', 'username');
	define('PASS', 'password');

	# Start the session
	$kt->KiteSession();

	# Check install state
	if($kt->CheckInstall('kt_settings')) {
		# Get site settings
		$Site = $kt->GetSiteSettings();
	}

	# Choose to enable/disable advertising (edit page /pages/advertising.php)
	$KiteAds = 0;

	# Connect
	$dbh = $ktSQL->kiteConnect(HOST, DBSE, USER, PASS);
?>