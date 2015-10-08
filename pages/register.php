<?php
	/*
		Kite URL Shortener
		Version 1.0.0
		iAlex (http://codecanyon.net/iAlex)
		register.php
	*/
	
	$kt = new Kite();

	# POST-ing a form
	if(!empty($_POST['ktEmail']) && !empty($_POST['ktPass'])) {
		# Attempt to register the user
		if($kt->RegisterUser($_POST['ktEmail'], $_POST['ktPass'])) {
			echo '<div class="alert alert-success" role="alert">Registration has been a success! You may now log in...</div>';
		}else{
			echo '<div class="alert alert-warning" role="alert">Sorry that email already exists in our database. Please try and log in...</div>';
		}
	}
?>
<h4>Register</h4>
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
				<button type="submit" class="btn btn-primary">Sign up</button>
			</div>
		</form>
	</div>
	<div class="col-md-1"></div>
</div>