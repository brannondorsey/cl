<?php require_once 'class.Database.inc.php';
class SearchHandler{

	public $search_table = "searches";
	public $location_table = "location";

	public function __construct(){

	}

	//returns the name of the current location or false if failed
	public function get_location(){
		$query = "SELECT * FROM " . $this->location_table;
		if($results = Database::get_all_results($query)) return $results['location_name'];
		return false;
	}

	//returns 2D array of live searches if there are any and false if none are found
	public function get_searches(){
		$query = "SELECT * FROM " . $this->search_table;
		if($results = Database::get_all_results($query)){
			if(is_array($results[0])) return $results;
			else return array($results);
		}
		else return false;
	}

	//returns an array of all of the live search query values. OLD DESCRIPTION.
	//called in the index page for the "searching" select box options
	public function get_live_search_column_vals($property_name){
		$live_searches = $this->get_searches();
		$properties = array();
		foreach($live_searches as $search){
			if(!in_array($search[$property_name], $properties)) $properties[] = $search[$property_name];
		}
		return $properties;
	}

	//deletes search rows from an array of ids. 
	//Returns true on success and false on failure.
	public function delete_searches($array_of_ids){
		$query = "DELETE FROM " . $this->search_table . " WHERE ";
		foreach($array_of_ids as $id){
			$query .= "id='" . $id ."' OR ";
		}
		$query = rtrim($query, " OR"); //remove trailing OR
		return Database::execute_sql($query);
	}

	//uses category list to lookup a human readable category name from a category code
	public function decode_category($category_code){
		$list = file("includes/categories_list.txt");
		foreach($list as $item){
			$pair = explode(">", $item);
			if($pair[0] == $category_code) return $pair[1];
		}
	}

	//boolean to determine if search already exists
	public function search_exists($search, $category){
		$query = "SELECT COUNT(*) FROM " . $this->search_table . " WHERE query='" . $search . "' AND category='" . $category . "'";
		$numb_rows = intval(Database::get_all_results($query)['COUNT(*)']);
		return ($numb_rows > 0) ? true : false;
	}

}
?>