<?php
	/*
		Kite URL Shortener
		Version 1.0.0
		iAlex (http://codecanyon.net/iAlex)
		stats.php
	*/
	
	$kt = new Kite();
	global $Site;
	
	if(!$kt->Loggedin()) {
		$kt->Go($Site[0]['website_address'] . 'p/login');
	}

	$DrawGraph = 1;
	
	# Get Kite information
	$KiteStat = $kt->GetKite($_GET['id'], $_SESSION['KiteUserID']);

	# Kite does not exist?
	if(!$KiteStat) {
		$kt->Go($Site[0]['website_address'] . 'p/404');
	}

	# Get the views
	$KiteViews = $kt->GetKiteViews($KiteStat[0]['hash']);

	# No views, do not draw
	if(!$KiteViews) {
		$DrawGraph = 0;
	}
?>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>

<h4>Statistics</h4>
<div class="row">
	<div class="col-md-3">
		<p>Kite ID: <a href="<?php echo $Site[0]['website_address'] . $KiteStat[0]['hash']; ?>"><?php echo $KiteStat[0]['hash']; ?></a></p>
	</div>

	<div class="col-md-9">
		<?php
			if(strlen($KiteStat[0]['url']) > 55) {
				echo '<p>Original: <a href="' . $KiteStat[0]['url'] . '">' . substr($KiteStat[0]['url'], 0, 55) . '</a> (..)</p>';
			}else{
				echo '<p>Original: <a href="' . $KiteStat[0]['url'] . '">' . $KiteStat[0]['url'] . '</a></p>';
			}
		?>
	</div>
</div>

<div class="row">
	<div class="col-md-3">
		<p># of Views: <?php echo number_format($KiteStat[0]['total_views']); ?></p>
	</div>
	
	<div class="col-md-9">
		<p>Created: <?php echo date('D \t\h\e dS F Y \a\t H:i', $KiteStat[0]['timestamp']); ?></p>
	</div>
</div>
<?php
	if($DrawGraph) {
		echo '<br /><div class="row">';
		echo '<div class="col-md-12">';
		echo '<h4>Views</h4>';
		echo '<div id="KiteStat" style="height: 250px;"></div>';
		echo '</div>';
		echo '</div>';
	}else{
		echo '<br /><br /><div class="well well-sm" align="center">A graph will be shown when your short has had some views!</div>';
	}
?>

<script>
	new Morris.Line({
		element: 'KiteStat',
		hideHover: true,
		lineWidth: 1,
		pointSize: 1,
		gridTextSize: 9,
		parseTime: false,
		data: [
			<?php
				if($DrawGraph) {
					foreach($KiteViews as $View) {
						echo '{ time: \'' . $View['datetime'] . '\', value: ' . $View['views'] . ' },';
					}
				}
			?>
		],
		xkey: 'time',
		ykeys: ['value'],
		yLabelFormat: function(y){return y != Math.round(y)?'':y;},
		labels: ['Views']
	});
</script>