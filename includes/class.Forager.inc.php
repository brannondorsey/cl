<?php 
require_once 'class.SearchHandler.inc.php';
require_once 'class.QueryFormer.inc.php';
require_once 'class.Database.inc.php';
class Forager{

	public $search_hand;
	protected $q_former; //dynamically forms queries
	protected $results_table = "results"; //table name for results table

	public function __construct(){
		$this->search_hand = new SearchHandler();
		$this->q_former = new QueryFormer($this->search_hand->search_table);
	}

	public function load_data(){

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
		$assoc_array['location'] = strtolower(trim($listing->span[1]->span->small, "() "));
		
		//parse the query from the url
		$query_name = strstr($url, "query=");
		$query_name = str_replace("query=", "", $query_name);
		$query_name = strstr($query_name, "&minAsk=", TRUE);

		$assoc_array['query']    = urldecode($query_name);
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
			$url = "http://" . $this->search_hand->get_location() . ".craigslist.com/search/" . $search['category'] . "?srchType=A&query=" . $search['query']
			. "&minAsk=" . $min . "&maxAsk=" . $max;
			$urls[] = $url;
		}
		return $urls;
	}

	//erases all data from the results table
	//returns true on success and false on failure
	public function delete_results_content(){
		$query = "DELETE FROM " . $this->results_table;
		return Database::execute_sql($query);
	}
}
?>