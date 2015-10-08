<?php
	/*
		Kite URL Shortener
		Version 1.0.0
		iAlex (http://codecanyon.net/iAlex)
		shorts.php
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

	# Get 20 Kites
	$KiteShorts = $kt->GetKites(0, $KitePage, 20);

	# Count Kites
	$cnKites = count($KiteShorts);
?>
<h4>Kite Management</h4>
<p>Below you are able to view all of the URLs that have been shortened using this system as well as all the statistics about the given URL.</p>
<?php
	# No shorts!
	if(!$cnKites) {
		echo '<br /><div class="well well-sm" align="center">There does not seem to be anything here...</div>';
	}else{
?>
<div class="table-responsive" style="border:none;">
	<table class="table table-hover">
		<thead>
			<tr>
				<th data-field="remove"></th>
				<th data-field="original">Original</th>
				<th data-field="hash">Hash</th>
				<th data-field="views">Views</th>
				<th data-field="creation">Creation</th>
				<th data-field="user">User</th>
				<th data-field="type">Method</th>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach($KiteShorts as $Kite) {
					# Generate the removal link
					$RemoveKite = $Site[0]['website_address'] . $Kite['hash'] . '?lh=' . $kt->LinkHash($Kite['hash'], $Site[0]['unique_string']);
					
					# Account type
					if($Kite['method'] == 'd') {
						$Type = 'Direct';
					}else if($Kite['method'] == 'a') {
						$Type = 'Api';
					}
					
					echo '<tr>';
					# Removal link
					echo '<td><a onclick="return confirm(\'Are you sure you would like to remove this short?\');" target="_blank" data-toggle="tooltip" data-placement="top" title="Remove" href="' . $RemoveKite . '" class="btn btn-danger btn-xs">';
					echo '<span class="glyphicon glyphicon-remove"></span>';
					echo '</a></td>';
					
					# Kite url check
					if(strlen($Kite['url']) > 55) {
						echo '<td><a href="' . $Kite['url'] . '">' . substr($Kite['url'], 0, 55) . '</a> (..)</td>';
					}else{
						echo '<td><a href="' . $Kite['url'] . '">' . $Kite['url'] . '</a></td>';
					}
					echo '<td>' . $Kite['hash'] . '</td>';
					echo '<td>' . number_format($Kite['total_views']) . '</td>';
					echo '<td>' . date('dS F Y \a\t H:i', $Kite['timestamp']) . '</td>';
					echo '<td>' . $Kite['user_id'] . '</td>';
					echo '<td>' . $Type . '</td>';
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