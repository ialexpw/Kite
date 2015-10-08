<?php
	/*
		Kite URL Shortener
		Version 1.0.0
		iAlex (http://codecanyon.net/iAlex)
		index.php
	*/

	include $_SERVER['DOCUMENT_ROOT'] . '/resources/config.php';

	# Not logging in or not admin
	if(!$kt->Loggedin() || $_SESSION['KiteAccType'] < 2) {
		$kt->Go($Site[0]['website_address'] . 'p/account');
	}

	# Set a short timeout for the version
	$ctx = stream_context_create(
		array(
			'http' => array(
				'timeout' => 1
			)
		)
	);

	# Get the current version of Kite
	$KiteVersion = @file_get_contents("http://kite.paq.nz/version.txt", 0, $ctx);

	# Get the user count
	$UserCount = number_format($kt->GetUserCount());

	# Get the Kite count
	$KiteCount = number_format($kt->GetKiteCount());
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Kite - Paste; Shrink; Share!</title>
		<meta charset="utf-8">
		<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
		<link href="<?php echo $Site[0]['website_address']; ?>resources/css/custom.css" rel="stylesheet">
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet">
		<link href='//fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
		<link href='//fonts.googleapis.com/css?family=Handlee' rel='stylesheet' type='text/css'>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<style>
			body {
				font-family:'Open Sans', sans-serif;
				background:#F5F5F5;
			}
			.logo {
				display: block;
    			margin-left: auto;
    			margin-right: auto;
				margin-bottom:12px;
		
			}
			.logo a {
				text-decoration:none;
				color:black;
			}
			.menulogo {
				height:40px;
				margin-top:-11px
			}
			.top-menu {
				margin-top:15px;
			}
			.white-bg {
				background:white;
			}
			.indent {
				margin-left:8px;
			}
			.indentx2 {
				margin-left:16px;
			}
			@-moz-document url-prefix() {
			  fieldset { display: table-cell; }
			}
		</style>
		<script src="//code.jquery.com/jquery-1.11.3.js"></script>
		<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>				
		<script type="text/javascript">
			$(function() {
				$(function () {
					$('[data-toggle="tooltip"]').tooltip()
				});
				
				$('.updateSettings').on('submit', function(e) {
					$.ajax({
						type: 'post',
						url: "<?php echo $Site[0]['website_address'] . basename(getcwd()); ?>/p/settings",
						data: $(".updateSettings").serialize(),
						success: function() {
							$('#updatetag').fadeIn().delay(3000).fadeOut();
						}
					});
					return false;
				});
			});
		</script>
	</head>
	
	<body>
		<nav class="navbar navbar-default">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>

				<a class="navbar-brand" href="<?php echo $Site[0]['website_address']; ?>"><img class="menulogo" src="<?php echo $Site[0]['website_address']; ?>img/logo_nt.png" /></a>
			</div>

			<div id="navbar" class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
					<li><a href="<?php echo $Site[0]['website_address']; ?>">Homepage</a></li>
					<li><a href="<?php echo $Site[0]['website_address']; ?>p/api-docs">API Documentation</a></li>
				</ul>
				
				<ul class="nav navbar-nav navbar-right">
					<?php
						# Show correct menu
						if($kt->Loggedin()) {
							# Admin link
							if(isset($_SESSION['KiteAccType']) && $_SESSION['KiteAccType'] >= 2) {
								echo '<li><a href="' . $Site[0]['website_address'] . 'admin/">Admin</a></li>';
							}
							echo '<li><a href="' . $Site[0]['website_address'] . 'p/account">Account</a></li>';
							echo '<li><a href="' . $Site[0]['website_address'] . 'p/logout">Logout</a></li>';
						}else{
							echo '<li><a href="' . $Site[0]['website_address'] . 'p/login">Login</a></li>';
							echo '<li><a href="' . $Site[0]['website_address'] . 'p/register">Register</a></li>';
						}
					?>
				</ul>
			</div>
		</nav>
		
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<a href="<?php echo $Site[0]['website_address'] . basename(getcwd()); ?>/"><img class="logo" src="<?php echo $Site[0]['website_address']; ?>img/logo_nosl.png" /></a>
				</div>
			</div>
			
			<div class="row">				
				<div class="col-md-12">
					<div class="well well-lg white-bg">
						<h4>Overview</h4>
						
						<p>Welcome to the Kite administation panel. This shows an overview of your current running system. Below you can see some basic stats of this Kite instance and by using
							the menu on the left you are able to look at a more <i>in-depth</i> view of Kite.</p>
						
						<hr>
						
						<div class="row">
							<div class="col-md-3">
								<div class="panel panel-default">
									<div class="panel-body">
										<h4 align="center">Users</h4>
										<p align="center"><?php echo $UserCount; ?> users</p>
										<a href="<?php echo $Site[0]['website_address'] . basename(getcwd()); ?>/p/users" class="btn btn-primary btn-sm btn-block"><span class="glyphicon glyphicon-cog"></span> Manage</a>
									</div>
								</div>
							</div>
							
							<div class="col-md-3">
								<div class="panel panel-default">
									<div class="panel-body">
										<h4 align="center">Shorts</h4>
										<p align="center"><?php echo $KiteCount; ?> shorts generated</p>
										<a href="<?php echo $Site[0]['website_address'] . basename(getcwd()); ?>/p/shorts" class="btn btn-primary btn-sm btn-block"><span class="glyphicon glyphicon-cog"></span> Manage</a>
									</div>
								</div>
							</div>
							
							<div class="col-md-3">
								<div class="panel panel-default">
									<div class="panel-body">
										<h4 align="center">Settings</h4>
										<p align="center">General site settings</p>
										<a href="<?php echo $Site[0]['website_address'] . basename(getcwd()); ?>/p/settings" class="btn btn-primary btn-sm btn-block"><span class="glyphicon glyphicon-cog"></span> Manage</a>
									</div>
								</div>
							</div>
							
							<div class="col-md-3">
								<div class="panel panel-default">
									<div class="panel-body">
										<h4 align="center">Version</h4>
										<p align="center">Kite Version 1.0.0</p>
										<?php
											# Up to date?
											if($KiteVersion == $KiteV) {
												echo '<a href="' . $Site[0]['website_address'] . basename(getcwd()) . '/p/changelog" class="btn btn-success btn-sm btn-block"><span class="glyphicon glyphicon-ok"></span> Up to Date</a>';
											}else if($KiteVersion > $KiteV){
												echo '<a target="_blank" href="http://codecanyon.net/user/ialex" class="btn btn-danger btn-sm btn-block"><span class="glyphicon glyphicon-remove"></span> Out of Date</a>';
											}else{
												echo '<a data-toggle="tooltip" data-placement="top" title="Possibly due to shared hosting restrictions!" target="_blank" href="http://codecanyon.net/user/ialex" class="btn btn-warning btn-sm btn-block"><span class="glyphicon glyphicon-exclamation-sign"></span> Unable to check</a>';
											}
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php
						# Page generation
						if(!empty($_GET['p'])) {
							$kt->KitePage($_GET['p']);
						}
					?>
					
					<!-- Footer -->
					<p>
						<a href="<?php echo $Site[0]['website_address']; ?>">Home</a> -
						<a href="<?php echo $Site[0]['website_address']; ?>p/api-docs">API Docs</a> -
						<a href="<?php echo $Site[0]['website_address']; ?>p/terms">Terms</a> -
						<a href="<?php echo $Site[0]['website_address']; ?>p/privacy">Privacy</a>
						<span class="pull-right">&copy; Kite</span>
					</p>
				</div>
			</div>
		</div>
	</body>
</html>