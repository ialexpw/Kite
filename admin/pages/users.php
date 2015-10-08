<?php
	/*
		Kite URL Shortener
		Version 1.0.0
		iAlex (http://codecanyon.net/iAlex)
		users.php
	*/

	$kt = new Kite();
	global $Site;
	$KiteLink = '';

	# Not logging in or not admin
	if(!$kt->Loggedin() || $_SESSION['KiteAccType'] < 2) {
		$kt->Go($Site[0]['website_address'] . 'p/account');
	}

	# Work out the page we are on
	if(!empty($_GET['page']) && is_numeric($_GET['page'])) {
		if(!$_GET['page']) {
			$KitePage = 1;
		}else{
			$KitePage = $_GET['page'];
			$KiteLink = '&page=' . $KitePage;
		}
	}else{
		$KitePage = 1;
	}

	# Change user levels
	if(!empty($_GET['a'])) { # Admin
		$kt->SetUserLevel($_GET['a'], 2);
	}else if(!empty($_GET['s'])) { # Standard
		$kt->SetUserLevel($_GET['s'], 1);
	}else if(!empty($_GET['d'])) { # Disabled
		$kt->SetUserLevel($_GET['d'], 0);
	}

	# Get 20 users
	$KiteUsers = $kt->GetUserList($KitePage, 20);

	# Count users
	$cnUsers = count($KiteUsers);
?>
<h4>User Management</h4>
<p>Below you are able to manage the users that have signed up to Kite. You are able to enable, disable, remove or upgrade their accounts.</p>
<?php
	# No shorts!
	if(!$cnUsers) {
		echo '<br /><div class="well well-sm" align="center">There does not seem to be anything here...</div>';
	}else{
?>
<div class="table-responsive" style="border:none;">
	<table class="table table-hover">
		<thead>
			<tr>
				<th data-field="id">ID</th>
				<th data-field="email">Email</th>
				<th data-field="apikey">Api Key</th>
				<th data-field="joined">Join Date</th>
				<th data-field="type">Account Type</th>
				<th data-field="type">Manage</th>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach($KiteUsers as $User) {
					# Account type
					if($User['type'] == 0) {
						$Type = 'Disabled';
					}else if($User['type'] == 1) {
						$Type = 'Standard';
					}else if($User['type'] == 2) {
						$Type = 'Administrator';
					}
					
					echo '<tr>';
					echo '<td>' . $User['id'] . '</td>';
					echo '<td>' . $User['email'] . '</td>';
					echo '<td>' . $User['apiKey'] . '</td>';
					echo '<td>' . date('D \t\h\e dS F Y \a\t H:i', $User['joined']) . '</td>';
					echo '<td>' . $Type . '</td>';
					echo '<td><a href="?a=' . $User['id'] . $KiteLink . '"><i class="fa fa-user-plus"></i></a> <a href="?s=' . $User['id'] . $KiteLink . '"><i class="fa fa-user"></i></i></a> <a href="?d=' . $User['id'] . $KiteLink . '"><i class="fa fa-user-times"></i></a></td>';
					echo '</tr>';
				}
			?>
		</tbody>
	</table>
</div>
<?php
	}
?>
<a href="?page=<?php echo $KitePage-1; ?>" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-arrow-left"></span> Previous</a>
<span class="pull-right"><a href="?page=<?php echo $KitePage+1; ?>" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-arrow-right"></span> Next</a></span>