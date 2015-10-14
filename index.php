<?php
	/*
		Kite URL Shortener
		Version 1.0.0
		iAlex (http://codecanyon.net/iAlex)
		index.php
	*/

	include 'resources/config.php';

	# Go to the installer if it is there
	if(file_exists('install') || is_dir('install')) {
		$kt->Go('/install/index.php?modal');
	}

	# Set invalid to 0
	$KiteInvalid = 0;

	# Show index
	$ShowIndex = 1;

	# Init variables
	$KiteShort = '';
	$KiteRemove = '';

	# We have a long url
	if(isset($_POST) && !empty($_POST['longUrl'])) {
		$KiteURL = $_POST['longUrl'];
		
		# Add to the URL if needed
		if($parts = parse_url($KiteURL)) {
			if(!isset($parts['scheme'])) {
				$KiteURL = 'http://' . $KiteURL;
			}
		}
		
		# See if the URL is valid
		if($kt->CheckURL($KiteURL, $Site[0]['filter_urls'])) {
			$KiteShort = $kt->GenerateKite($KiteURL, $Site[0]['url_length']);
			$GetKite = $Site[0]['website_address'] . $KiteShort;
			
			# Removal link
			$KiteRemove = $kt->LinkHash($KiteShort, $Site[0]['unique_string']);
			
			# No need to show the page at the bottom
			$ShowIndex = 0;
		}else{
			$_GET['p'] = 'invalid';
			$KiteInvalid = 1;
		}
	}

	# We have a URL hash
	if(!empty($_GET['k'])) {
		$KiteHash = $_GET['k'];

		# See if the hash exists
		if($kt->CheckShort($KiteHash)) {
			# Are we removing it?
			if(!empty($_GET['lh'])) {
				$LinkHash = $_GET['lh'];
				
				# Ensure the hash is correct
				if($kt->LinkHash($KiteHash, $Site[0]['unique_string'], $LinkHash)) {
					$kt->RemoveKite($KiteHash);
					
					# Log it
					if(isset($_SESSION['KiteUserID'])) {
						$kt->LogAction('1', $_SESSION['KiteUserID'], $KiteHash);
					}else{
						$kt->LogAction('1', 0, $KiteHash);
					}
					
					# Send to a template page
					$_GET['p'] = 'link-removed';
					$KiteInvalid = 1;
				}else{
					# Log it
					if(isset($_SESSION['KiteUserID'])) {
						$kt->LogAction('2', $_SESSION['KiteUserID'], $KiteHash);
					}else{
						$kt->LogAction('2', 0, $KiteHash);
					}
					
					$_GET['p'] = '404';
					$KiteInvalid = 1;
				}
			}else{
				# Is there a password for this URL?
				if($kt->PasswordSet($KiteHash)) {
					# There is! Send them to the right page
					$kt->Go($Site[0]['website_address'] . 'p/password?kite=' . $KiteHash);
				}else{
					# Add a view to the URL
					$kt->AddView($KiteHash);

					# Translate the hash into a URL
					$GetURL = $kt->GetURLFromHash($KiteHash);

					# Send the user to the URL
					$kt->Go($GetURL[0]['url']);
				}
			}
		}else{
			$_GET['p'] = '404';
			$KiteInvalid = 1;
		}
	}

	# We have an API request
	if(!empty($_GET['apiKey'])) {
		# Store the API key
		$KiteApiKey = $_GET['apiKey'];
		
		# Verify the API key
		if(!$kt->CheckApiKey($KiteApiKey)) {
			exit($kt->BuildResponse('2', 'invalid_apikey'));
		}
		
		# Shorten
		if(!empty($_GET['longUrl'])) {
			# Get the long URL
			$KiteURL = $_GET['longUrl'];
			
			# Add to the URL if needed
			if($parts = parse_url($KiteURL)) {
				if(!isset($parts['scheme'])) {
					$KiteURL = 'http://' . $KiteURL;
				}
			}
			
			# Check Api requests
			$KiteUser = $kt->GetUserFromApi($KiteApiKey);
			
			# Api usage
			if($kt->GetApiUsage($KiteUser[0]['id']) >= $Site[0]['api_usage']) {
				exit($kt->BuildResponse('5', 'reached_api_limit'));
			}
			
			# See if the URL is valid
			if($kt->CheckURL($KiteURL, $Site[0]['filter_urls'])) {
				$GetKite = $Site[0]['website_address'] . $kt->GenerateKite($KiteURL, $Site[0]['url_length'], 'a', $KiteApiKey);

				# Output in plain text
				if(isset($_GET['text'])) {
					exit($GetKite);
				}else{
					exit($kt->BuildResponse('0', addslashes($GetKite)));
				}
			}else{
				exit($kt->BuildResponse('1', 'invalid_url'));
			}
		}
		
		# Expand or get info
		if(!empty($_GET['shortUrl'])) {
			# Get the short URL
			$KiteURL = $_GET['shortUrl'];
			
			if($kt->CheckShort($KiteURL)) {
				# Expand
				if(isset($_GET['expand'])) {
					$ExpandKite = $kt->GetURLFromHash($KiteURL);
					exit($kt->BuildResponse('0', addslashes($ExpandKite[0]['url'])));
				}

				# Views
				if(isset($_GET['views'])) {
					$KiteViews = $kt->GetURLFromHash($KiteURL);
					exit($kt->BuildResponse('0', $KiteViews[0]['total_views']));
				}
			}else{
				exit($kt->BuildResponse('4', 'invalid_short'));
			}
		}
		
		# If it gets here, we are missing params
		exit($kt->BuildResponse('3', 'missing_params'));
	}

	# Setting a password
	if(!empty($_GET['pwset']) && !empty($_GET['lh']) && !empty($_POST['pwField'])) {
		$LockKite = $_GET['pwset'];
		$KiteLinkHash = $_GET['lh'];
		
		# The password
		$LinkPassword = $_POST['pwField'];
		
		# Check the link hash is correct before setting the password
		if($kt->LinkHash($LockKite, $Site[0]['unique_string'], $KiteLinkHash)) {
			$kt->SetPassword($LockKite, $LinkPassword);
		}
	}
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
				
				$('#pwform').on('submit', function(e) {
					$.ajax({
						type: 'post',
						url: "<?php echo $Site[0]['website_address'] ?>?pwset=<?php echo $KiteShort . '&lh=' . $KiteRemove; ?>",
						data: $("#pwform").serialize(),
						success: function() {
							$('#setpass').fadeIn().delay(3000).fadeOut();
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

				<a class="navbar-brand" href="<?php echo $Site[0]['website_address'] ?>"><img class="menulogo" src="<?php echo $Site[0]['website_address'] ?>img/logo_nt.png" /></a>
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
					<a href="<?php echo $Site[0]['website_address']; ?>"><img class="logo" src="<?php echo $Site[0]['website_address']; ?>img/logo_nosl.png" /></a>
					<div class="well well-lg white-bg">
						<form action="<?php echo $Site[0]['website_address']; ?>" method="post" class="">
							<div class="input-group">
								<input type="text" class="form-control input-lg" id="longUrl" name="longUrl" placeholder="Paste; Shrink; Share!" required>
								<span class="input-group-btn">
									<button class="btn btn-lg btn-success" type="submit">Shorten</button>
								</span>
							</div>
						</form>
						<?php
							if(!empty($GetKite)) {								
								# No point showing share links if it is invalid!
								if(!$KiteInvalid) {
									echo '<br /><input type="text" class="form-control input-lg" value="' . $GetKite . '" onclick="this.select()">';
									
									echo '<br />';
									echo '<div class="row">';
									echo '<div class="col-md-7">';
									
									echo '<div class="panel panel-info">';
									echo '<div class="panel-heading">';
									echo '<h3 class="panel-title">Share your link</h3>';
									echo '</div>';
									echo '<div class="panel-body" align="center">';
									
									# Facebook share link
									echo '<a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=' . $GetKite . '">';
									echo '<img style="margin-right:10px;" src="' . $Site[0]['website_address'] . 'img/facebook.png" /></a>';
									
									# Google+ share link
									echo '<a target="_blank" href="https://plus.google.com/share?url=' . $GetKite . '">';
									echo '<img style="margin-right:10px;" src="' . $Site[0]['website_address'] . 'img/gplus.png" /></a>';
									
									# Twitter share link
									echo '<a target="_blank" href="https://twitter.com/home?status=' . $GetKite . '">';
									echo '<img style="margin-right:10px;" src="' . $Site[0]['website_address'] . 'img/twitter.png" /></a>';
									
									# Pinterest share link
									echo '<a target="_blank" href="https://pinterest.com/pin/create/button/?url=' . $GetKite . '&media=&description=Kite Short">';
									echo '<img style="margin-right:10px;" src="' . $Site[0]['website_address'] . 'img/pinterest.png" /></a>';
									
									echo '</div>';
									echo '</div></div>';
									
									# Link information
									echo '<div class="col-md-5">';
									echo '<div class="panel panel-info">';
									echo '<div class="panel-heading">';
									echo '<h3 class="panel-title">Link information</h3>';
									echo '</div>';
									echo '<div class="panel-body">';
									
									echo '<span>Set a password<span id="setpass" class="pull-right" style="display:none;">password set</span></span>';
									echo '<form action="" method="post" id="pwform" class="pwform">';
									echo '<div class="input-group">';
									echo '<input type="text" class="form-control input-sm" id="pwField" name="pwField" placeholder="Password...">';
									echo '<span class="input-group-btn">';
									echo '<button class="btn btn-sm btn-primary" type="submit">Set</button>';
									echo '</span>';
									echo '</form>';
									echo '</div>';
									
									echo '<br />';
									
									echo '<span>Removal link<span class="pull-right" style="color:red;">only shown once</span></span>';
									echo '<input type="text" class="form-control input-sm" value="' . $GetKite . '?lh=' . $KiteRemove . '" onclick="this.select()">';
									
									echo '</div></div></div>';
									
									echo '</div>';
								}
							}
						?>
					</div>
					
					<?php
						# Page generation
						if(!empty($_GET['p'])) {
							$kt->KitePage($_GET['p']);
						}else if(empty($_GET['p']) && $KiteAds) {
							$kt->KitePage('advertising');
						}else{
							if($ShowIndex) {
								$kt->KitePage('index');
							}
						}
					?>
					
					<!-- Footer -->
					<p>
						<?php
							# Admin link
							if(isset($_SESSION['KiteAccType']) && $_SESSION['KiteAccType'] >= 2) {
								echo '<a href="' . $Site[0]['website_address'] . 'admin/">Admin</a> -';
							}
						?>
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