<?php
require_once("class.Database.inc.php");

class QueryFormer {

	public $default_output_limit = 100;

	protected $columns_to_provide = "id, name, url, price, location, query, category, recency_rating";
	protected $max_output_limit = 250;
	protected $default_order_by = "ORDER BY id ";
	protected $default_flow = "ASC ";
	protected $search = "";
	protected $no_results_message = "no results found";
	protected $table;

	public function __construct($_table){
		$this->table = $_table;
	}

	//builds a dynamic MySQL query statement from a $_GET array. Array must be sanitized before using this function.
	public function form_query(&$get_array){

		$column_parameters = array();
		$columns_to_provide_array = explode(', ', $this->columns_to_provide);
		$order_by = "";
		$flow = "";
		$limit = "";
		$page = 1;
		$exact = false;
		$count_only = false;

		//distribute $_GETs to their appropriate arrays/vars
		foreach($get_array as $parameter => $value){
			if($this->is_column_parameter($parameter, $columns_to_provide_array)){ 
				$column_parameters[$parameter] = $value;
			}
			else if($parameter == 'order_by') $order_by = $value;
			else if($parameter == 'flow') $flow = $value;
			else if($parameter == 'limit') $limit = $value;
			else if($parameter == 'page') $page = (int) $value;
			else if($parameter == 'exact' &&
				    strtolower($value) == "true") $exact = true;
			else if($parameter == 'count_only' &&
				    strtolower($value) == "true" ||
				    $parameter == 'count_only' &&
				    $value == true){
				$count_only = true;
			} 

		}
		if($count_only) $query = "SELECT COUNT(*)";
		else $query = "SELECT " . $this->columns_to_provide;
		$query .= " FROM "  . $this->table ." ";

		//add WHERE statements
		if(sizeof($column_parameters) > 0){
			$i = 0;
			$query .= "WHERE ";
			foreach ($column_parameters as $parameter => $value) {
				//if exact parameter was specified as TRUE 
				//or column parameter is id search by = not LIKE
				if($parameter == 'id' || $exact){
					$this->append_prepend($value, "'");
				 	$query .= "$parameter = $value ";
				}
				else $query .= "$parameter LIKE '%$value%' ";
				if($i != sizeof($column_parameters) -1) $query .= "AND ";
				$i++;
			}
		}
	
		//add ORDER BY statement
		$order_by_string;
		if($order_by != "" &&
		$this->is_column_parameter($order_by, $columns_to_provide_array)){
			$order_by_string = "ORDER BY $order_by ";
		}
		else $order_by_string = $this->default_order_by;
		$query .= $order_by_string;

		//add FLOW statement
		$flow_string;
		$flow = strtoupper($flow);
		if($flow != "" &&
		$flow == 'ASC' ||
		$flow == 'DESC'){
			$flow_string = "$flow ";
		}
		else $flow_string = $this->default_flow;
		$query .= $flow_string;

		//only add LIMIT of it is not a COUNT query
		if(!$count_only){
			//add LIMIT statement
			$limit_string;
			if($limit != ""){
				$limit = (int) $limit;
				if((int) $limit > $this->max_output_limit) $limit = $this->max_output_limit;
				if((int) $limit < 1) $limit = 1;
				$limit_string = "LIMIT $limit";
			} 
			else{
				$limit = $this->default_output_limit;
				$limit_string = "LIMIT $limit";	
			} 
			$query .= $limit_string;
		}

		//add PAGE statement
		if($page != "" &&
			$page > 1){
			$query .= " OFFSET " . $limit * ($page -1);
		}
		
		return $query;
	}

//------------------------------------------------------------------------------
//HELPERS

	//appends and prepends slashes to string for WHERE statement values
	protected function append_prepend(&$string, $char){
		$string = $char . $string . $char;
	}

	//checks if a parameter string is also the name of a SELECT statement's requested column
	protected function is_column_parameter($parameter_name, $columns_to_provide_array){
		return in_array ($parameter_name, $columns_to_provide_array);
	}

}

?>