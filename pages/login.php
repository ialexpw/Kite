<?php
	/*
		Kite URL Shortener
		Version 1.0.0
		iAlex (http://codecanyon.net/iAlex)
		login.php
	*/

	$kt = new Kite();
	global $Site;
	
	# POST-ing the login
	if(!empty($_POST['ktEmail']) && !empty($_POST['ktPass'])) {
		# Attempt to log in
		if($kt->LoginUser($_POST['ktEmail'], $_POST['ktPass'])) {
			# Disabled?
			if($_SESSION['KiteAccType'] == 0) {
				session_destroy();
				$kt->Go($Site[0]['website_address'] . 'p/disabled');
			}else{
				$kt->Go($Site[0]['website_address'] . 'p/account');
			}
		}else{
			echo '<div class="alert alert-warning" role="alert">Sorry you have entered either a wrong username or password. Please try again...</div>';
		}
	}
?>
<h4>Log in</h4>
<br />
<div class="row">
	<div class="col-md-1"></div>
	<div class="col-md-10">
		<form class="form-horizontal" method="post" align="center">
			<div class="form-group">
				<input type="email" class="form-control" id="ktEmail" name="ktEmail" placeholder="Email">
			</div>
			<div class="form-group">
				<input type="password" class="form-control" id="ktPass" name="ktPass" placeholder="Password">
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-primary">Sign in</button>
			</div>
		</form>
	</div>
	<div class="col-md-1"></div>
</div>