<?php
	require_once 'includes/class.Forager.inc.php';
	require_once 'includes/class.Database.inc.php';
	require_once 'includes/class.Session.inc.php';

	error_reporting(E_ALL ^ E_NOTICE);

	Database::init_connection();
	Session::start();

	if(isset($_POST) &&
	   !empty($_POST)){
		$error = ""; //holds an error if it occurs
		$post_array = Database::clean($_POST);
		
		//handles loggin if not logged in and autorization code is posted and correct
		if(!Session::is_logged_in() &&
			isset($post_array['authorization']) &&
			$post_array['authorization'] == Session::$auth_code){
			Session::login();
		}
	}

	if(Session::is_logged_in()){
		$forager = new Forager();
		$live_search_queries = $forager->search_hand->get_live_search_column_vals("query"); //get the queries of all live searches
		$live_search_categories = $forager->search_hand->get_live_search_column_vals("category"); //get all categories currently being live searched
		$results_exist = false;
		if(isset($_GET) &&
			!empty($_GET)){
			$get_array = Database::clean($_GET);
			//if the form's get vals are set and filled
			if(isset($get_array['searching']) &&
				!empty($get_array['searching']) &&
				isset($get_array['order_by']) &&
				!empty($get_array['order_by'])){

				$query_array = array();

				if($get_array['searching'] != "all") $query_array['query'] = $get_array['searching'];

				//handle the order_by and flow "parameters"
				if($get_array['order_by'] == 'recency_rating' ||
				   $get_array['order_by'] == 'query' ||
				   $get_array['order_by'] == 'location'){
				   	$query_array['order_by'] = $get_array['order_by'];
				}else{
					switch($get_array['order_by']){
						case 'price low':
							$query_array['order_by'] = 'price'; 
							$query_array['flow'] = 'asc';
							break;
						case 'price high':
							$query_array['order_by'] = 'price'; 
							$query_array['flow'] = 'desc';
							break;
						default:
							$query_array['order_by'] = 'recency_rating';
							$query_array['flow'] = 'desc'; 

					}
				}

				if(isset($get_array['category']) &&
					!empty($get_array['category']) &&
					$get_array['category'] != "all"){
					 $query_array['category'] = $get_array['category'];
				}
				
				//get the results
				$total_numb_results = $forager->get_total_numb_results($query_array);
				$numb_results = $forager->q_former->default_output_limit;
				$page = (isset($_GET['page'])) ? floatval($_GET['page']) : 1; //get the current page
				$query_array['page'] = $page; //add the page to the assoc array
				$total_pages = ceil($total_numb_results/$numb_results); //calculates total number of pages
				$live_search_results = $forager->load_results($query_array);
				$results_exist = true;
			}
		}
	}

?>

<!DOCTYPE html>
	<html>
		<head>
			<title>Results</title>
			<link rel="stylesheet" type="text/css" href="css/base.css">
			<?php 
			//if the get vals are specified load and use jquery to set default select options
			if(isset($get_array['searching']) &&
			!empty($get_array['searching']) ||
			isset($get_array['order_by']) &&
			!empty($get_array['order_by']) ||
			isset($get_array['category']) &&
			!empty($get_array['category'])){ ?>
			<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" ></script>
			<script>
				$('document').ready(function(){
					<?php if(!empty($get_array['searching'])) { ?> $("select#searching").val(<?php echo '"' . $get_array['searching'] . '"'?>); <?php } ?>
					<?php if(!empty($get_array['order_by'])) { ?> $("select#order_by").val(<?php echo '"' . $get_array['order_by'] . '"'?>); <?php } ?>
					<?php if(!empty($get_array['category'])) { ?> $("select#category").val(<?php echo '"' . $get_array['category'] . '"'?>); <?php } ?>
				});
			</script>
			<?php } ?>
		</head>

		<body>
			<?php if(Session::is_logged_in()) { ?>
			<?php require_once 'includes/navbar.inc.php'; ?>
			<div class="results-filter">
				<form method="get">
					Searching:
					<select name="searching" id="searching">
						<option value="all">all</option>
						<?php foreach($live_search_queries as $query){?>
							<option value="<?php echo $query?>"><?php echo $query?></option>
						<?php } ?>
					</select>
					<span>
						In:
						<select name="category" id="category">
							<option value="all">all</option>
							<?php foreach($live_search_categories as $category){?>
							<option value="<?php echo $category?>"><?php echo $forager->search_hand->decode_category($category)?></option>
							<?php } ?>
						</select>
					</span>
					<span>
						Order By:
						<select name="order_by" id="order_by">
							<option value="recency_rating">Newest</option>
							<option value="query">Searches</option>
							<option value="location">Neighborhood</option>
							<option value="price low">Low Price</option>
							<option value="price high">High Price</option>
						</select>
					</span>
					<input type="Submit" value="Filter results"  id="button">
				</form>
			</div>
			<div class="live-search-result-container">
				<?php if($results_exist){ 
					$previous_result;
					foreach($live_search_results as $result){ 
						if(isset($result['url'])){?>
							<?php if($_GET['order_by'] == 'query' &&
							       $result['query'] != $previous_result['query']) { ?>
							<div class="search-seperator">
								<?php echo $result['query'];?>
							</div>
							<?php } ?>
						<div class="live-search-result">
							<a href="<?php echo $result['url']?>"><?php echo $result['name']?></a>
							<?php if($result['price'] != intval(0)) { ?> <span class="price"><?php echo "$" . $result['price']?></span> <?php } ?>
							<span class="listing-location"><?php echo ucfirst($result['location']); ?></span>
						</div>
			<?php $previous_result = $result; //assign the previous result
				  }
				}
			} ?>
			</div>
			<span class="page-count"><?php if($results_exist) echo min($page, $total_pages) . " of " . $total_pages ?></span>
			<?php //if there is more than 100 results
			if($total_numb_results > $numb_results) {
			$page_url = "index.php?" . http_build_query($_GET);?>
			<div class="page-number-container">
				 <?php if ($page > 1) { ?>
                    <a class="prev" href="<?php echo $forager->remove_page_parameter($page_url); ?>&amp;page=<?php echo ($page - 1); ?>">previous</a>
                    <?php }
                     if ($page < $total_pages) { ?>
                    <a class="next" href="<?php echo $forager->remove_page_parameter($page_url); ?>&amp;page=<?php echo ($page + 1); ?>">next</a>
                    <?php } ?>
			</div>
			<?php } 
			} else require_once 'includes/login_form.inc.php'; ?>
		</body>
	</html>