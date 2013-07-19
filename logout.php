<?php
	require_once 'includes/class.Session.inc.php';
	require_once 'includes/class.Database.inc.php';
	Session::start();
	Session::destroy();
	header("Location: index.php?searching=all&category=all&order_by=recency_rating", TRUE);
?>