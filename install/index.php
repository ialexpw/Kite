<?php
	/*
		Kite URL Shortener
		Version 1.0.0
		iAlex (http://codecanyon.net/iAlex)
		index.php
	*/

	include '../resources/config.php';

	# Init variable
	$TablesImport = 0;

	# POSTed form (messy but it's only the installer!)
	if(!empty($_POST['userName']) && 
	   !empty($_POST['userPass']) && 
	   !empty($_POST['webTitle']) && 
	   !empty($_POST['webAddr']) && 
	   !empty($_POST['apiLimit']) && 
	   !empty($_POST['urlLength']) && 
	   !empty($_POST['perPage']) && 
	   !empty($_POST['uniqueVal'])) {
		
		# Save the variables
		$KtUser = $_POST['userName'];
		$KtPass = password_hash($_POST['userPass'], PASSWORD_DEFAULT);
		$KtTitl = $_POST['webTitle'];
		$KtAddr = $_POST['webAddr'];
		$KtApiL = $_POST['apiLimit'];
		$KtUrlL = $_POST['urlLength'];
		$KtPerP = $_POST['perPage'];
		$KtUniQ = $_POST['uniqueVal'];
		
		# Check they added an ending slash, if not, add one!
		if(substr($KtAddr, -1) != '/') {
			$KtAddr = $KtAddr . '/';
		}

		# Holds the current query
		$sqlQ = '';
		
		# Read the SQL file
		$readSQL = file('Kite.sql');
		
		# Go through each line
		foreach ($readSQL as $SQL) {
			# Ignore comments and empty parts
			if(substr($SQL, 0, 2) == '--' || $SQL == '') {
				continue;
			}

			# Add the line to the current segment
			$sqlQ .= $SQL;
			
			# If a semi-colon it's at the end of the query
			if(substr(trim($SQL), -1, 1) == ';') {
				$stmt = $dbh->query($sqlQ);
				
				# Reset the temp variable
				$sqlQ = '';
			}
		}
		
		# Current timestamp
		$cTime = time();
		
		# Insert the settings
		$data = array( 'website_title' => $KtTitl, 'website_address' => $KtAddr, 'api_usage' => $KtApiL, 'url_length' => $KtUrlL, 'items_perpage' => $KtPerP, 'unique_string' => $KtUniQ, 'identifier' => 'settings' );
		$stmt = $dbh->prepare("INSERT INTO kt_settings (website_title, website_address, api_usage, url_length, items_perpage, unique_string, identifier) VALUES (:website_title, :website_address, :api_usage, :url_length, :items_perpage, :unique_string, :identifier)");
		$stmt->execute($data);
		
		# Insert the new user
		$data = array( 'email' => $KtUser, 'password' => $KtPass, 'joined' => $cTime, 'type' => 2 );
		$stmt = $dbh->prepare("INSERT INTO kt_users (email, password, joined, type) VALUES (:email, :password, :joined, :type)");
		$stmt->execute($data);
		
		# Get the last INSERT ID
		$lastInsert = $dbh->lastInsertId();

		# Generate an Api key
		$UserApi = PseudoCrypt::hash($lastInsert, 10);
		$UserApi .= PseudoCrypt::hash($lastInsert*5, 10);
		$UserApi .= PseudoCrypt::hash($lastInsert+5, 10);

		# Update the row with the Api key
		$stmt = $dbh->prepare("UPDATE kt_users SET apiKey = :apiKey WHERE id = :id");
		$stmt->bindParam(':apiKey', $UserApi);
		$stmt->bindParam(':id', $lastInsert);
		$stmt->execute();
		
		# Success variable
		$TablesImport = 1;
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Kite - Paste; Shrink; Share!</title>
		<meta charset="utf-8">
		<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
		<link href="../resources/css/custom.css" rel="stylesheet">
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
			<?php if(isset($_GET['modal']) && !$TablesImport) { ?>
				$(window).load(function(){
					$('#installModal').modal('show');
				});
			<?php }else if($TablesImport) { ?>
				$(window).load(function(){
					$('#installedModal').modal('show');
				});
			<?php } ?>
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

				<a class="navbar-brand" href=""><img class="menulogo" src="../img/logo_nt.png" /></a>
			</div>

			<div id="navbar" class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
					<li><a href="../index.php">Homepage</a></li>
					<li><a href="../p/api-docs">API Documentation</a></li>
				</ul>
				
				<ul class="nav navbar-nav navbar-right">
					<?php
						# Show correct menu
						if($kt->Loggedin()) {
							# Admin link
							if(isset($_SESSION['KiteAccType']) && $_SESSION['KiteAccType'] >= 2) {
								echo '<li><a href="../admin/">Admin</a></li>';
							}
							echo '<li><a href="../p/account">Account</a></li>';
							echo '<li><a href="../p/logout">Logout</a></li>';
						}else{
							echo '<li><a href="../p/login">Login</a></li>';
							echo '<li><a href="../p/register">Register</a></li>';
						}
					?>
				</ul>
			</div>
		</nav>
		
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<img class="logo" src="../img/logo_nosl.png" />
					<div class="well well-lg white-bg">
						<h3>Kite Installer</h3>
						<p>Welcome to the installer for Kite. I have tried to keep this as simple as possible so I have attempted to fill out a lot of defaults for you! Please review the values below
						and change what you need to and then hit the install button at the bottom. All of these settings (apart from the unique id) can be changed after install.</p>
						
						<p>Before installing Kite, please ensure you have entered the correct MySQL details inside /resources/config.php and that the database exists already, Kite does not create the database itself.</p>
						
						<hr>
						
						<form action="" method="post" class="">
							<div class="row">
								<div class="col-md-6">
									<span>What email address would you like to use as the Administrator log in?</span>
									<input type="email" class="form-control input" id="userName" name="userName" placeholder="example@email.tld" required>
								</div>
								
								<div class="col-md-6">
									<span>Enter a secure password to use for this Administrator!</span>
									<input type="password" class="form-control input" id="userPass" name="userPass" placeholder="secure password" required>
								</div>
							</div>
							
							<br />
							
							<span>What would you like the title of the website to be? This is the text that is shown on the browser tab!</span>
							<input type="text" class="form-control input" id="webTitle" name="webTitle" value="Kite - Paste; Shrink; Share!" required>
							
							<br />
							
							<span>What website is this hosted on? We've done our best to fill this in, but correct us if we are wrong!</span>
							<input type="text" class="form-control input" id="webAddr" name="webAddr" value="<?php echo 'http://' . $_SERVER['SERVER_NAME'] .'/'; ?>" required>
							
							<br />
							
							<span>How many API requests would you like users to use? This is the maximum allowed in a 24 hour period!</span>
							<input type="number" class="form-control input" id="apiLimit" name="apiLimit" value="1000" required>
							
							<br />
							
							<span>How long would you like the short URLs to be? This is the amount of characters after the main URL (<?php echo $_SERVER['SERVER_NAME'] .'/<b>123456</b>'; ?>)</span>
							<input type="number" class="form-control input" id="urlLength" name="urlLength" value="6" required>
							
							<br />
							
							<span>Once you log in, you can view your URLs that you have shortened. How many would you like to show per page?</span>
							<input type="number" class="form-control input" id="perPage" name="perPage" value="8" required>
							
							<br />
							
							<span>This is for your unique identifier, it is for creating removal links and link password validation - just enter lots of random text!</span>
							<input type="text" class="form-control input" id="uniqueVal" name="uniqueVal" value="sd]{@kfkh*uGYUG-u£ft2yF5566!TufGDFG56$+_=D^54%?GFDF_7gvb" required>
							<br />
							<input type="submit" class="btn btn-primary btn-sm btn-block" value="I'm strapped in and ready, install Kite for me!"/>
						</form>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal fade" id="installModal">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Installing Kite</h4>
					</div>
					<div class="modal-body">
						<img class="logo" src="../img/logo_nosl.png" /><br />
						<p>You have been taken to the installer as it appears you have not installed Kite! :-)</p>
						
						<p>If you have already installed Kite, you will need to remove the /install/ directory to disable this alert.</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal fade" id="installedModal">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Congratulations</h4>
					</div>
					<div class="modal-body">
						<img class="logo" src="../img/logo_nosl.png" /><br />
						<p>You have successfully installed Kite!</p>
						
						<p>Before doing anything else you <b>must</b> remove this /install/ directory. You can then visit the homepage of your brand new Kite install!</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>