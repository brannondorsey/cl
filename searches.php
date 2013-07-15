<?php require_once 'includes/class.Database.inc.php';
	Database::init_connection();
	$searchHand = new SearchHandler();
	$live_searches = $searchHand->get_searches();
	Database::close_connection();
?>