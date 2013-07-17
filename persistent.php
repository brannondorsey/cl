<?php
	require_once 'includes/class.Forager.inc.php';
	require_once 'includes/class.Database.inc.php';

	//settings
	$minutes_between_reloads = 5;
	$max_loadtime_in_seconds = 60;

	error_reporting(E_ALL ^ E_NOTICE);
	$timeout_in_millis = $minutes_between_reloads*60*1000;
	$max_loadtime_in_millis = $max_loadtime_in_seconds*1000;
	set_time_limit($max_loadtime_in_seconds);

	Database::init_connection();
	$forager = new Forager();
	$urls = $forager->get_urls();
	//delete all content from the database to ensure that results are fresh
	$forager->delete_results_content();
	foreach ($urls as $url) {
		$forager->add_page_contents_to_db($url);
	}

?>

<!DOCTYPE html>
	<html>
		<head>
			<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" ></script>
			<script>
				$('document').ready(function(){
					setTimeout("location.reload(true);",<?php echo $timeout_in_millis?>);
				});
			</script>
			<title>Persistent</title>
			<link rel="stylesheet" type="text/css" href="css/base.css">
		</head>

		<body>
			<p class="persistent-message">Leave this page in an open tab. It refreshes itself every <?php echo $minutes_between_reloads ?> minutes and updates the database.</p>
		</body>
	</html>