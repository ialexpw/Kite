<?php
	/*
		Kite URL Shortener
		Version 1.0.0
		iAlex (http://codecanyon.net/iAlex)
		account.php
	*/
	
	$kt = new Kite();
	global $Site;
	
	# If not logged in
	if(!$kt->Loggedin()) {
		$kt->Go($Site[0]['website_address'] . 'p/login');
	}

	# Init the search term variable
	$searchTerm = '';

	# Work out the page we are on
	if(!empty($_GET['page']) && is_numeric($_GET['page'])) {
		if(!$_GET['page']) {
			$KitePage = 1;
		}else{
			$KitePage = $_GET['page'];
		}
	}else{
		$KitePage = 1;
	}

	# Get the shorts
	if(!empty($_POST['srcBar']) && ctype_alnum($_POST['srcBar'])) {
		# The search term
		$searchTerm = $_POST['srcBar'];
		
		# On search terms do not limit the perpage value
		$UserKites = $kt->GetKites($_SESSION['KiteUserID'], $KitePage, 500, $searchTerm);
		
		# Result amount
		$resCount = count($UserKites);
	}else{
		$UserKites = $kt->GetKites($_SESSION['KiteUserID'], $KitePage, $Site[0]['items_perpage']);
	}

	# How many have we got?
	$cnKite = count($UserKites);
?>
<h4>
	Welcome <?php echo $_SESSION['KiteEmail']; ?>
	<?php
		if(!empty($searchTerm)) {
			echo '<span class="pull-right">' . $resCount . ' results for <i>"' . $searchTerm . '"</i></span>';
		}
	?>
</h4>

<hr>
<h5>Search</h5>
<form class="form-horizontal" action="<?php echo $Site[0]['website_address']; ?>p/account" method="post">
	<div class="input-group">
		<input type="text" class="form-control input-sm" id="srcBar" name="srcBar" placeholder="Search by Kite ID or original URL.. (letters and numbers only)">
			<span class="input-group-btn">
			<button class="btn btn-sm btn-success" type="submit">Search</button>
		</span>
	</div>
</form>
<hr>

<?php
	# No shorts!
	if(!$cnKite) {
		echo '<br /><div class="well well-sm" align="center">There does not seem to be anything here...</div>';
	}else{
?>
<div class="table-responsive" style="border:none;">
	<table class="table table-hover">
		<thead>
			<tr>
				<th width="7%" data-field="drop"></th>
				<th width="25%" data-field="id">Kite ID</th>
				<th data-field="original">Original URL</th>
				<th data-field="created">Created</th>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach($UserKites as $Kite) {
					# Generate the removal link
					$RemoveKite = $Site[0]['website_address'] . $Kite['hash'] . '?lh=' . $kt->LinkHash($Kite['hash'], $Site[0]['unique_string']);
					
					echo '<tr>';
					echo '<td>';
					echo '<a data-toggle="tooltip" data-placement="top" title="Statistics" style="margin-right:3px;" href="' . $Site[0]['website_address'] . 'p/stats?id=' . $Kite['id'] . '" class="btn btn-default btn-xs">';
					echo '<span class="glyphicon glyphicon-stats"></span>';
					echo '</a>';
					
					echo '<a onclick="return confirm(\'Are you sure you would like to remove this short?\');" target="_blank" data-toggle="tooltip" data-placement="top" title="Remove" href="' . $RemoveKite . '" class="btn btn-danger btn-xs">';
					echo '<span class="glyphicon glyphicon-remove"></span>';
					echo '</a></td>';
					
					echo '<td><a target="_blank" href="' . $Site[0]['website_address'] . $Kite['hash'] . '">' . $Kite['hash'] . '</a></td>';

					# Neaten the link if it is long
					if(strlen($Kite['url']) > 55) {
						echo '<td><a target="_blank" href="' . $Kite['url'] . '">' . substr($Kite['url'], 0, 55) . '</a> (..)</td>';
					}else{
						echo '<td><a target="_blank" href="' . $Kite['url'] . '">' . $Kite['url'] . '</a></td>';
					}
					
					echo '<td>' . date('dS F Y \a\t H:i', $Kite['timestamp']) . '</td>';

					echo '</tr>';
				}
			?>
		</tbody>
	</table>
	<hr>
</div>
<?php
	}
?>
<a href="?page=<?php echo $KitePage-1; ?>" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-arrow-left"></span> Previous</a>
<span class="pull-right"><a href="?page=<?php echo $KitePage+1; ?>" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-arrow-right"></span> Next</a></span>