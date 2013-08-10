<?php
//*****************************************************************************
//*****************************************************************************
/**
* Database Functions Plugin
*
* @package		phpOpenPlugins
* @subpackage	Database
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 7/17/2012, Last updated: 3/1/2013
**/
//*****************************************************************************
//*****************************************************************************

//=============================================================================
//=============================================================================
// Get Number of Records Function
//=============================================================================
//=============================================================================
function get_num_recs($ds, $table, $fields, $quoted_fields=false)
{
	$ds = (string)$ds;

	//-------------------------------------------------------------
	// Pull Default Data Source if passed data source is empty
	//-------------------------------------------------------------
	if ($ds == '') {
		if (isset($_SESSION["default_data_source"])) {
			$ds = $_SESSION["default_data_source"];
		}
		else {
			trigger_error("Error: " . __FUNCTION__ . "():: Invalid data source!");
			return false;
		}
	}

	if (is_array($quoted_fields) && count($quoted_fields > 0)) {
		foreach ($quoted_fields as $field) { $quoted_fields[$field] = $field; }
	}
	if (is_array($fields) && count($fields > 0)) {
		$strsql = "select count(*) as count from {$table}";
		$count = 0;
		foreach ($fields as $name => $value) {
			$strsql .= (!$count) ? (" where") : (" and");
			$strsql .= (isset($quoted_fields[$name])) ? (" {$name} = '{$value}'") : (" {$name} = {$value}");
			$count++;
		}
		$num_recs = qdb_lookup($ds, $strsql, "count");
		settype($num_recs, "integer");
		return $num_recs;
	}
	else { return 0; }
}

//=============================================================================
//=============================================================================
// Remove Child Records Function
//=============================================================================
//=============================================================================
function remove_child_recs($ds, $field, $value)
{
	$ds = (string)$ds;

	//-------------------------------------------------------------
	// Pull Default Data Source if passed data source is empty
	//-------------------------------------------------------------
	if ($ds == '') {
		if (isset($_SESSION["default_data_source"])) {
			$ds = $_SESSION["default_data_source"];
		}
		else {
			trigger_error("Error: " . __FUNCTION__ . "():: Invalid data source!");	
			return false;
		}
	}

	//-------------------------------------------------------------
	// Pull a list of tables that have the named field
	//-------------------------------------------------------------
	$strsql = "show tables";
	$tables = qdb_list($ds, $strsql);

	$table_index = "Tables_in_" . $_SESSION[$ds]["source"];
	foreach ($tables as $table) {
		$table_name = $table[$table_index];
		$strsql = "show columns from {$table_name} where Field = '{$field}'";
		$fields = qdb_list($ds, $strsql);
		if (count($fields) > 0) {
			qdb_list($ds, "delete from {$table_name} where {$field} = {$value}");
		}
	}
}

//=============================================================================
//=============================================================================
// Make MySQL Bind Parameters from Array Function
//=============================================================================
//=============================================================================
if (!function_exists('make_mysql_bind_parameters')) {
	function make_mysql_bind_parameters(&$params, $type, &$values)
	{
		if (!is_array($params)) { $params = array(); }
	
		$ret_val = false;
		if (!is_array($values)) {
			if (isset($params[0])) { $params[0] .= (string)$type; }
			else { $params[0] = (string)$type; }
			$params[] = &$values;
			$ret_val = '?';
		}
		else {
			if (!isset($params[0])) { $params[0] = ''; }
			foreach ($values as &$val) {
				$params[0] .= (string)$type;
				$params[] = &$val;
				if ($ret_val) { $ret_val .= ', '; }
				$ret_val .= '?';
			}
		}
		return $ret_val;
	}
}

?>
