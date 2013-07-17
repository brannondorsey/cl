<?php 
require_once 'includes/class.Database.inc.php';
require_once 'includes/class.SearchHandler.inc.php';
	Database::init_connection();
	$search_hand = new SearchHandler();
	$b_error = false;

	//if a search was added add it to the `searches` table
	if(isset($_POST) &&
	   !empty($_POST)){
		$error = ""; //holds an error if it occurs
		$post_array = Database::clean($_POST);
		//if the query was specified...
		if(!empty($post_array['query'])){
			//if the search does not already exist add it to the `searches` table
			if(!$search_hand->search_exists($post_array['query'], $post_array['category'])) Database::execute_from_assoc($_POST, $search_hand->search_table);
			//otherwise add an error
			else $error= "search already exists";
		}else $error = "search was not specified";
		//if the location code was changed update the database
		if(!empty($post_array['location'])){
			Database::execute_from_assoc(array('location_name' => $post_array['location']), $search_hand->location_table, "UPDATE", "location_name");
		}
		//loop through post array to see if rows need to be deleted
		$rows_to_delete = array();
		foreach($post_array as $key => $value){
			if(preg_match("/delete/", $key) == 1) $rows_to_delete[] = $value;
		}
		//if rows were deleted remove them from the db
		if(!empty($rows_to_delete)) $search_hand->delete_searches($rows_to_delete); 
		if($error != "") $b_error = true;
	}
	$live_searches = $search_hand->get_searches(); //load search results
?>

<!DOCTYPE html>
	<html>
		<head>
		<title>Searches</title>
		<link rel="stylesheet" type="text/css" href="css/base.css">
		</head>

		<body>
			<?php if($b_error) {?>
			<div class="error">
				<?php echo "Error: " . $error ?>
			</div>
			<?php }?>
			<div class="new-search">
				<form class="" method="post" action="">
					search for: <input type="text" name="query" placeholder="search">
					in: <select name="category">
						<?php require_once 'includes/categories.inc.php'; ?>
					</select>
					price: <input type="text" name="min" placeholder="min">
					<input type="text" name="max" placeholder="max">
					<input type="submit" value="submit">
				</form>
			</div>
			<div class="location">
				<form method="post">
					Location code: <input type="text" name="location" value="<?php echo $search_hand->get_location()?>">
					<input type="submit" value="Save">
				</form> 
			</div>
			<form method="post" action="">
				<div class="live-searches">
				<?php if(!empty($live_searches) &&
						$live_searches != FALSE){
					// if(!is_array($live_searches[0])) $live_searches = array($live_searches); //if $live_searches is 1D because there is only one result make it 2D
					//if there is more than one result
					foreach ($live_searches as $search) {
						$max = (floatval($search['max']) == 0) ? "none" : "$" . $search['max'];
						$min = (floatval($search['min']) != 0 ||
						        $max != "none") ? "$" . $search['min'] : "none"; ?>
					<div class="search">
						Search: <span><?php echo $search['query']?></span> Category: <span><?php echo $search_hand->decode_category($search['category'])?></span> Min : <span><?php echo $min?></span> Max : <span><?php echo $max?></span>
						<span class="delete">Delete: <input type="checkbox" name="delete-<?php echo $search['id']; ?>" value="<?php echo $search['id']; ?>"></span>
					</div>
			    <?php }
				}else echo "No searches"; ?>
				</div>
				<?php if(!empty($live_searches)){?>
				<input type="submit" value="Delete Searches" class="delete-submit">
				<?php } ?>
			</form>
		</body>
	</html>