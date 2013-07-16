<?php
	require_once 'includes/class.Forager.inc.php';
	require_once 'includes/class.Database.inc.php';

	Database::init_connection();
	$forager = new Forager();
	$urls = $forager->get_urls();
	foreach ($urls as $url) {
		$forager->add_page_contents_to_db($url);
	}


?>

<!DOCTYPE html>
	<html>
		<head>
			<title>Persistent</title>
			<link rel="stylesheet" type="text/css" href="css/base.css">
		</head>

		<body>
			
		</body>
	</html>