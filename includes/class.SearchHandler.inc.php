<?php require_once 'class.Database.inc.php';
class SearchHandler{

	protected $search_table = "searches";

	public function _construct(){

	}

	public function update_searches(){
		
	}

	//returns 2D (or 1D if one result) array of live searches
	public function get_searches(){
		$query = "SELECT * FROM " . $search_table;
		return Database::get_all_results($query);
	}

}
?>