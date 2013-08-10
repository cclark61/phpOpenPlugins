<?php
//*****************************************************************************
//*****************************************************************************
/**
* Database Analysis Plugin
*
* @package		phpOpenPlugins
* @subpackage	Database Analysis
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 3-28-2007, Last updated: 3-20-2010
**/
//*****************************************************************************
//*****************************************************************************

//*******************************************************************************
/**
* Compare 2 Database Tables
* 
* @param string Data Source 1
* @param string Table 1
* @param string Data Source 2
* @param string Table 2
* @return Array Field Analysis
*/
//*******************************************************************************
function table_analyze($src1, $table1, $src2, $table2)
{
	// Table #1 Fields
	$db1_fields = table_fields($src1, $table1);
	$db1_type = (isset($_SESSION[$src1]['type'])) ? ($_SESSION[$src1]['type']) : (false);
		
	// Table #2 Fields
	$db2_fields = table_fields($src2, $table2);
	$db2_type = (isset($_SESSION[$src2]['type'])) ? ($_SESSION[$src2]['type']) : (false);

	// Set Database Specific Field Indices
	$field_indices = array();
	
	// PostgreSQL
	$field_indices['pgsql'] = array();
	$field_indices['pgsql']['type'] = 'udt_name';
	$field_indices['pgsql']['max_length'] = 'character_maximum_length';
	$field_indices['pgsql']['nullable'] = 'is_nullable';
	$field_indices['pgsql']['default'] = 'column_default';

	// MySQL
	$field_indices['mysql'] = array();
	$field_indices['mysql']['type'] = 'Type';
	$field_indices['mysql']['max_length'] = 'Type';
	$field_indices['mysql']['nullable'] = 'Null';
	$field_indices['mysql']['default'] = 'Default';

	// MySQLi
	$field_indices['mysqli'] = array();
	$field_indices['mysqli']['type'] = 'Type';
	$field_indices['mysqli']['max_length'] = 'Type';
	$field_indices['mysqli']['nullable'] = 'Null';
	$field_indices['mysqli']['default'] = 'Default';

	// Set Counter Defaults
	$master_field_list = array();
	$field_list = array();
	$totals = array(0, 0);
	$mismatches = 0;

	// DB #1 Tables
	foreach ($db1_fields as $key => $value) {
		if (isset($db2_fields[$key])) {
			$field_list[$key]['both'] = 1;
			$match = 1;
			
			// 1st Table
			$field_list[$key][$src1]['type'] = $value[$field_indices[$db1_type]['type']];
			$field_list[$key][$src1]['max_length'] = $value[$field_indices[$db1_type]['max_length']];
			$field_list[$key][$src1]['nullable'] = $value[$field_indices[$db1_type]['nullable']];
			$field_list[$key][$src1]['default'] = $value[$field_indices[$db1_type]['default']];

			// 2nd table
			$field_list[$key][$src2]['type'] = $db2_fields[$key][$field_indices[$db2_type]['type']];
			$field_list[$key][$src2]['max_length'] = $db2_fields[$key][$field_indices[$db2_type]['max_length']];
			$field_list[$key][$src2]['nullable'] = $db2_fields[$key][$field_indices[$db2_type]['nullable']];
			$field_list[$key][$src2]['default'] = $db2_fields[$key][$field_indices[$db2_type]['default']];
			
			if ($value[$field_indices[$db1_type]['type']] != $db2_fields[$key][$field_indices[$db2_type]['type']]) { $match = 0; }
			if ($value[$field_indices[$db1_type]['max_length']] != $db2_fields[$key][$field_indices[$db2_type]['max_length']]) { $match = 0; }
			if ($value[$field_indices[$db1_type]['nullable']] != $db2_fields[$key][$field_indices[$db2_type]['nullable']]) { $match = 0; }
			if ($value[$field_indices[$db1_type]['default']] != $db2_fields[$key][$field_indices[$db2_type]['default']]) { $match = 0; }

			$field_list[$key]['match'] = $match;
			if ($match != 1) { $mismatches++; }
			unset($db2_fields[$key]);
			$totals[1]++;
		}
		else {
			$field_list[$key]['both'] = 0;
			$field_list[$key]['match'] = 0;
			$field_list[$key][$src1]['type'] = $value[$field_indices[$db1_type]['type']];
			$field_list[$key][$src1]['max_length'] = $value[$field_indices[$db1_type]['max_length']];
			$field_list[$key][$src1]['nullable'] = $value[$field_indices[$db1_type]['nullable']];
			$field_list[$key][$src1]['default'] = $value[$field_indices[$db1_type]['default']];
			$mismatches++; 
		}
		$totals[0]++;
	}

	// DB #2 Tables
	foreach ($db2_fields as $key => $value) {	
		$field_list[$key]['both'] = 0;
		$field_list[$key]['match'] = 0;
		$field_list[$key][$src2]['type'] = $value[$field_indices[$db2_type]['type']];
		$field_list[$key][$src2]['max_length'] = $value[$field_indices[$db2_type]['max_length']];
		$field_list[$key][$src2]['nullable'] = $value[$field_indices[$db2_type]['nullable']];
		$field_list[$key][$src2]['default'] = $value[$field_indices[$db2_type]['default']];
		$mismatches++;
		$totals[1]++;
	}
	
	$master_field_list['mismatches'] = $mismatches;
	$master_field_list['totals'] = $totals;
	$master_field_list['field_list'] = $field_list;
	
	ksort($master_field_list['field_list']);
	return $master_field_list;
}

//*******************************************************************************
/**
* Get Database Tables
* 
* @param string Data Source
**/
//*******************************************************************************
function db_tables($src)
{
	// Set default result
	$db_tables = false;
	
	// Check if data source is valid
	if (!isset($_SESSION[$src])) {
		trigger_error("[db_tables] Error: Unknown Database Source!! ($src)");
	}
	else {
		// Database
		$db = $_SESSION[$src]['source'];
		
		// Schema
		$schema = (isset($_SESSION[$src]['schema'])) ? ($_SESSION[$src]['schema']) : (false);
		
		// Build SQL Statement based on DB Type
		if ($_SESSION[$src]['type'] == 'mysql' || $_SESSION[$src]['type'] == 'mysqli') {
			$strsql = "show tables from $db";
			$index = "Tables_in_$db";
		}
		else if ($_SESSION[$src]['type'] == 'pgsql') {
			$strsql = "select DISTINCT(table_name) from information_schema.columns";
			$strsql .= " WHERE table_catalog = '$db'";
			if ($schema) {
				 $strsql .= " and table_schema = '$schema'";
			}
			$strsql .= " order by table_name";
			$index = "table_name";
		}
		
		// Get Tables
		if (isset($strsql)) {
			$data1 = new data_trans($src);
			$data1->data_query($strsql);
			$db_tables = $data1->data_key_assoc($index);
		}
		else { $db_tables = array(); }
	}
	
	// Return Tables
	return $db_tables;
}

//*******************************************************************************
/**
* Get Table Fields
* 
* @param string Data Source
* @param string Table
**/
//*******************************************************************************
function table_fields($src, $table)
{
	// Set default result
	$db_fields = false;
	
	// Check if data source is valid
	if (!isset($_SESSION[$src])) {
		trigger_error("[table_fields] Error: Unknown Database Source!! ($src)");
	}
	else {
		// Database
		$db = $_SESSION[$src]['source'];
		
		// Schema
		$schema = (isset($_SESSION[$src]['schema'])) ? ($_SESSION[$src]['schema']) : (false);
		
		// Build SQL Statement based on DB Type
		if ($_SESSION[$src]['type'] == 'mysql' || $_SESSION[$src]['type'] == 'mysqli') {
			$strsql = "show columns from $table";
			$index = "Field";
		}
		else if ($_SESSION[$src]['type'] == 'pgsql') {
			$strsql = "SELECT * FROM information_schema.columns";
			$strsql .= " WHERE table_catalog = '$db'";
			if ($schema) {
				 $strsql .= " and table_schema = '$schema'";
			}
			$strsql .= " and table_name = '$table'";
			$strsql .= " order by column_name";
			$index = "column_name";
		}
		
		// Get fields
		$data1 = new data_trans($src);
		$data1->data_query($strsql);
		$db_fields = $data1->data_key_assoc($index);
	}
	
	// Return Fields
	return $db_fields;
}

?>
