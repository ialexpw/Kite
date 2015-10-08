<?php
	/*
		Kite URL Shortener
		Version 1.0.0
		iAlex (http://codecanyon.net/iAlex)
		settings.php
	*/

	$kt = new Kite();
	global $Site;

	# Not logging in or not admin
	if(!$kt->Loggedin() || $_SESSION['KiteAccType'] < 2) {
		$kt->Go($Site[0]['website_address'] . 'p/account');
	}

	# POSTing a form
	if(!empty($_POST)) {
		$kt->UpdateSettings($_POST);
	}
?>
<h4>Site Settings<span id="updatetag" class="pull-right" style="display:none;">settings updated</span></h4>

<!-- Website title -->
<form action="" method="post" class="updateSettings">
	<span>Website title (shows up on the browser tab)</span>
	<div class="input-group">
		<input type="text" class="form-control input-sm" id="setTitle" name="setTitle" value="<?php echo $Site[0]['website_title']; ?>">
		<span class="input-group-btn">
			<button class="btn btn-sm btn-success" type="submit">Update</button>
		</span>
	</div>
</form>

<br />

<!-- Website -->
<form action="" method="post" class="updateSettings">
	<span>Website (including the trailing slash!)</span>
	<div class="input-group">
		<input type="text" class="form-control input-sm" id="setSite" name="setSite" value="<?php echo $Site[0]['website_address']; ?>">
		<span class="input-group-btn">
			<button class="btn btn-sm btn-success" type="submit">Update</button>
		</span>
	</div>
</form>

<br />

<!-- API Usage -->
<form action="" method="post" class="updateSettings">
	<span>API usage (per 24 hour period)</span>
	<div class="input-group">
		<input type="text" class="form-control input-sm" id="setApi" name="setApi" value="<?php echo $Site[0]['api_usage']; ?>">
		<span class="input-group-btn">
			<button class="btn btn-sm btn-success" type="submit">Update</button>
		</span>
	</div>
</form>

<br />

<!-- URL Length -->
<form action="" method="post" class="updateSettings">
	<span>Short URL length (default is 6)</span>
	<div class="input-group">
		<input type="text" class="form-control input-sm" id="setShort" name="setShort" value="<?php echo $Site[0]['url_length']; ?>">
		<span class="input-group-btn">
			<button class="btn btn-sm btn-success" type="submit">Update</button>
		</span>
	</div>
</form>

<br />

<!-- Kites per page -->
<form action="" method="post" class="updateSettings">
	<span>Items per page (how many short URLs to show in the <a target="_blank" href="<?php echo $Site[0]['website_address']; ?>p/account">account</a> page)</span>
	<div class="input-group">
		<input type="text" class="form-control input-sm" id="setPage" name="setPage" value="<?php echo $Site[0]['items_perpage']; ?>">
		<span class="input-group-btn">
			<button class="btn btn-sm btn-success" type="submit">Update</button>
		</span>
	</div>
</form>

<br />

<!-- Filter URLs -->
<form action="" method="post" class="updateSettings">
	<span>Filter URLs (deny any URL that contains any of these, comma separated)</span>
	<div class="input-group">
		<input type="text" class="form-control input-sm" id="setFilter" name="setFilter" value="<?php echo $Site[0]['filter_urls']; ?>">
		<span class="input-group-btn">
			<button class="btn btn-sm btn-success" type="submit">Update</button>
		</span>
	</div>
</form>

<br />

<!-- Unqiue string -->
<form action="" method="post" class="updateSettings">
	<span>Unique encoding string (generates the removal link and sets the passwords for links, do not change after install)</span>
		<input type="text" class="form-control input-sm" id="setString" name="setString" value="<?php echo $Site[0]['unique_string']; ?>" disabled>
</form>