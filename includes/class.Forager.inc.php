<?php 
require_once 'class.SearchHandler.inc.php';
require_once 'class.QueryFormer.inc.php';
require_once 'class.Database.inc.php';
class Forager{

	public $search_hand;
	protected $q_former; //dynamically forms queries

	public function __construct(){
		$this->search_hand = new SearchHandler();
		$this->q_former = new QueryFormer($this->search_hand->search_table);
	}

	public function load_data(){

	}

	public function add_page_contents_to_db($url){
		if($page = file_get_contents($url)){
			if($craigslist_results = strstr($page, '<blockquote id="toc_rows">')){ 
				$craigslist_results = strstr($craigslist_results, '</blockquote>', TRUE);
				// disable PHP errors
				$old = libxml_use_internal_errors(true);
				$dom = new DOMDocument;
				$dom->loadHTML($craigslist_results);
				// restore the old behaviour
				libxml_use_internal_errors($old);
				$craigslist = simplexml_import_dom($dom);
				echo $craigslist->div[0];
				//die($craigslist->asXML());
				// try{$craigslist = new SimpleXMLElement(htmlspecialchars_decode($craigslist_results), LIBXML_NOEMPTYTAG);}
				// catch(Exception $e){}
			}else echo "No results for this search <br>";
		}
		else echo "URL did not open <br>";
	}

	//returns array of craigslist urls formed from searches on success and false on failure
	//$results_array must be 2D
	public function get_urls(){
		$query = "SELECT * FROM " . $this->search_hand->search_table;
		if($results = Database::get_all_results($query));
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
}
?>