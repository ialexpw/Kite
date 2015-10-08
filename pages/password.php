<?php
	/*
		Kite URL Shortener
		Version 1.0.0
		iAlex (http://codecanyon.net/iAlex)
		password.php
	*/

	$kt = new Kite();
	global $Site;

	# Incorrect message
	$IncorrectPass = 0;

	# If empty/invalid url, then why are we here?
	if(!empty($_GET['kite'])) {
		$Kite = $_GET['kite'];
		# Incorrect short or password is not set
		if(!$kt->CheckShort($Kite) || !$kt->PasswordSet($Kite)) {
			$kt->Go($Site[0]['website_address']);
		}
	}else{
		$kt->Go($Site[0]['website_address']);
	}

	# POST'ed a password
	if(!empty($_POST) && !empty($_POST['kPass'])) {
		# Verify the password
		$Password = $_POST['kPass'];
		
		# If correct go to the URL
		if($kt->VerifyPassword($Kite, $Password)) {
			# Add a view to the URL
			$kt->AddView($Kite);

			# Translate the hash into a URL
			$GetURL = $kt->GetURLFromHash($Kite);

			# Send the user to the URL
			$kt->Go($GetURL[0]['url']);
		}else{
			$IncorrectPass = 1;
		}
	}

	# Alert message
	if($IncorrectPass) {
		echo '<div class="alert alert-warning" role="alert">Sorry that is the incorrect password, please try entering it again...</div>';
	}
?>
<h4>Enter Password</h4>
<p>This URL requires a password to view, please enter the correct password below and you will be taken to the website.</p>
<form action="" method="post" id="verpw" class="verpw">
	<div class="input-group">
		<input type="text" class="form-control input-sm" id="kPass" name="kPass" placeholder="Password...">
		<span class="input-group-btn">';
			<button class="btn btn-sm btn-primary" type="submit">Verify Password</button>
		</span>
	</div>
</form>