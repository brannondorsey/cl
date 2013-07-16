<?php
	require_once 'includes/class.Forager.inc.php';
	require_once 'includes/class.Database.inc.php';

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
			<title>Persistent</title>
			<link rel="stylesheet" type="text/css" href="css/base.css">
		</head>

		<body>
			
		</body>
	</html>