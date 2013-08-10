<?php
//*****************************************************************************
//*****************************************************************************
/**
* Pagination Functions Plugin
*
* @package		phpOpenPlugins
* @subpackage	Pagination
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 7/17/2012, Last updated: 1/29/2013
**/
//*****************************************************************************
//*****************************************************************************

//=============================================================================
//=============================================================================
// paginated_records()
// Pagination function returns a multi-dimensional array:
// results[0] = record_set
// results[1] = totals for non-limited record set
// results[2] = total number of pages
// Defaults: page = 1, max_rows = 10, db_source = "" (default_data_source)
//=============================================================================
//=============================================================================
function paginated_records($qp)
{
	// Variable Declarations / Presets
	$return_arr = false;
	extract($qp);
	if (!isset($db_source)) { $db_source = ""; }
	if (!isset($fields)) { $fields = "*"; }
	if (!isset($page) || !is_numeric($page) || $page <= 0) { $page = 1; }
	if (!isset($max_rows)) { $max_rows = 10; }
	if (!isset($table)) { return false; }
	
	// Base SQL Statement
	$sql_count = "select count(*) as row_count from {$table}";
	$strsql = "select {$fields} from {$table}";
	
	// Where Clause
	if (isset($where)) {
		$sql_count .= " " . $where;
		$strsql .= " " . $where;
	}
	
	// Group By Clause
	if (isset($group_by)) { $strsql .= " " . $group_by; }
	
	// Order By Clause
	if (isset($order_by)) { $strsql .= " " . $order_by; }
	
	// Limit Clause
	$start = ($page - 1) * $max_rows;
	$strsql .= " limit $start, $max_rows";
	
	// Build return array
	$return_arr["row_count"] = qdb_lookup($db_source, $sql_count, "row_count");
	$return_arr["num_pages"] = ceil($return_arr["row_count"] / $max_rows);
	$return_arr["records"] = qdb_list($db_source, $strsql);

	return($return_arr);
}

//=============================================================================
//=============================================================================
// paginated_links()
// Prints a pagination list on paginated links
//=============================================================================
//=============================================================================
function paginated_links($num_pages, $base_link, $curr_page, $args=false)
{
	// Default Settings
	$separator = ' | ';
	$div_class = 'pages_box';
	$text = 'Pages: ';
	$div_attrs = array();

	// Arguments / Parameters
	if ($args && is_array($args)) { extract($args); }
	ob_start();

	// Div Class
	if ($div_class) { $div_attrs['class'] = $div_class; }

	print $text;
	for ($i = 1; $i <= $num_pages; $i++) {
		if ($i != 1 && $separator !== false) { print $separator; }
		if ($curr_page != $i) {
			$link = $base_link . $i;
			print anchor($link, $i);
		}
		else { print $i; }
	}
	print div(ob_get_clean(), $div_attrs);
}

?>
