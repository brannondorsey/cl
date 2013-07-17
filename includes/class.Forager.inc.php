<?php 
require_once 'class.SearchHandler.inc.php';
require_once 'class.QueryFormer.inc.php';
require_once 'class.Database.inc.php';
class Forager{

	public $search_hand;
	public $q_former; //dynamically forms queries
	protected $results_table = "results"; //table name for results table

	public function __construct(){
		$this->search_hand = new SearchHandler();
		$this->q_former = new QueryFormer($this->results_table);
	}

	//returns a 2D array of table results from an assoc array of craigslist flavored "indexd api parameters"
	//assoc array must be cleaned before being passed into this method
	public function load_results($assoc_array){
		$query = $this->q_former->form_query($assoc_array);
		//echo $query;
		$results = Database::get_all_results($query);
		$results = (is_array($results[0])) ? $results: array($results);
		return $results;
	}

	//adds contents of a url to the results table in the db
	//called from the persistent page
	public function add_page_contents_to_db($url){
		if($page = file_get_contents($url)){
			 if($craigslist_results = strstr($page, '<blockquote id="toc_rows">')){
			    if($craigslist_results = strstr($craigslist_results, '</blockquote>', TRUE)){ 
					// disable PHP errors
					$old = libxml_use_internal_errors(true);
					$dom = new DOMDocument;
					$dom->loadHTML("<html>" . $craigslist_results . "</html>");
					// restore the old behaviour
					libxml_use_internal_errors($old);
					$craigslist = simplexml_import_dom($dom);
					//for each listing...
					foreach ($craigslist->body->blockquote->p as $listing){
						//parse the listing html to get an assoc array that matches the results database
						$listing_assoc = $this->get_listing_content($listing, $url);
						$listing_assoc = Database::clean($listing_assoc);
						//and execute the sql to add it to the database
						if(!Database::execute_from_assoc($listing_assoc, $this->results_table)) echo "Not added to the database";
					}
			 	}
			 }else echo "No results for this search <br>";
		}
		else echo "URL did not open <br>";
	}

	//parses listing (<p>)html and returns assoc array of listing content with key values 
	//that correspond with the results table columns
	protected function get_listing_content($listing, $url){
		$assoc_array = array();
		$assoc_array['name']     = (string) $listing->span[0]->a;
		$assoc_array['url']      = (strstr($listing->a['href'], "http")) ? $listing->a['href'] : strstr($url, "/search", TRUE) . $listing->a['href'];
		$assoc_array['price']    = trim($listing->span[1]->span->span[0]->span, "$ ");
		$assoc_array['location'] = ucwords(strtolower((trim($listing->span[1]->span->small, "() "))));
		
		//parse the query from the url
		$query_name = strstr($url, "query=");
		$query_name = str_replace("query=", "", $query_name);
		$query_name = strstr($query_name, "&minAsk=", TRUE);

		$assoc_array['query']    = urldecode($query_name);

		//parse the category code from the url
		$category_code = strstr($url, "search/");
		$category_code = str_replace("search/", "", $category_code);
		$category_code = strstr($category_code, "?", TRUE);

		$assoc_array['category'] = $category_code;

		return $assoc_array;
	}

	//returns array of craigslist urls formed from searches on success and false on failure
	//$results_array must be 2D
	public function get_urls(){
		$query = "SELECT * FROM " . $this->search_hand->search_table;
		if($results = Database::get_all_results($query)){
			$results = (is_array($results[0])) ? $results: array($results);
			// if(is_array($results[0])) return $results;
			// else return array($results);
		}
		else return false;
		$urls = array();
		foreach ($results as $search){
			$min = (floatval($search['min']) != 0) ? floatval($search['min']) : "";
			$max = (floatval($search['max']) != 0) ? floatval($search['max']) : "";
			$url = "http://" . $this->search_hand->get_location() . ".craigslist.com/search/" . $search['category'] . "?srchType=A&query=" . urlencode($search['query'])
			. "&minAsk=" . $min . "&maxAsk=" . $max;
			$urls[] = $url;
		}
		return $urls;
	}

	//returns the total number of results from an assoc array. 
	//Array must be pre cleaned
	public function get_total_numb_results($assoc_array){
		$assoc_array['count_only'] = true;
		$query = $this->q_former->form_query($assoc_array);
		$results = Database::get_all_results($query);
		if(isset($results['COUNT(*)'])) return $results['COUNT(*)'];
		else return false;
	}

	//erases all data from the results table
	//returns true on success and false on failure
	public function delete_results_content(){
		$query = "DELETE FROM " . $this->results_table;
		return Database::execute_sql($query);
	}

	//removes a TRAILING page GET parameter key value pair from a url string
	//called in index to handle previous and next pages
	public function remove_page_parameter($url){
		if($_url = strstr($url, "&page="));
		else return $url; //url did not contain page parameter so return it
		//return $page_pair;
		return str_replace($_url, "", $url);
	}
}
?>