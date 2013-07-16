<?php
	require_once 'includes/class.Forager.inc.php';
	require_once 'includes/class.Database.inc.php';
	require_once 'includes/class.Database.inc.php';

	Database::init_connection();
	$forager = new Forager();
	$live_search_queries = $forager->search_hand->get_live_search_queries(); //get the queries of all live searches
	if(isset($_GET) &&
		!empty($_GET)){
		$get_array = Database::clean($_GET);
		//if the form's get vals are set and filled
		if(isset($get_array['searching']) &&
			!empty($get_array['searching']) &&
			isset($get_array['order_by']) &&
			!empty($get_array['order_by'])){

			$query_array = array( 'query' => $get_array['searching'],
								  'order_by' => $get_array['order_by']);
			if(isset($get_array['category']) &&
				!empty($get_array['category'])) $query_array['category'] = $get_array['category'];
			#rest of code for this...
		}
	}

?>

<!DOCTYPE html>
	<html>
		<head>
			<title>Results</title>
			<link rel="stylesheet" type="text/css" href="css/base.css">
		</head>

		<body>
			<div class="results-filter">
				<form method="get">
					Searching:
					<select name="searching">
						<option value="all">all</option>
						<?php foreach($live_search_queries as $query){?>
							<option value="<?php echo $query?>"><?php echo $query?></option>
						<?php } ?>
					</select>
					<span>
						In:
						<select name="category">
							<?php require_once 'includes/categories.inc.php'; ?>
						</select>
					</span>
					<span>
						Order By:
						<select name="order_by">
							<option value="id">Newest</option>
							<option value="query">Searches</option>
							<option value="location">Neighborhood</option>
							<option value="price low">Low Price</option>
							<option value="price high">High Price</option>
						</select>
					</span>
					<input type="Submit" value="Filter results"  id="button">
				</form>
			</div>
		</body>
	</html>