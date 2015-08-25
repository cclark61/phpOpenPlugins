<?php
//*****************************************************************************
//*****************************************************************************
/**
* MySQL Functions Plugin
*
* @package		phpOpenPlugins
* @subpackage	Database
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 8/24/2015, Last updated: 8/25/2015
**/
//*****************************************************************************
//*****************************************************************************

//*******************************************************************************
//*******************************************************************************
// POP MySQL Object
//*******************************************************************************
//*******************************************************************************
class POP_mysql
{

	//=========================================================================
	//=========================================================================
	// Get Record by ID Function
	//=========================================================================
	// Previosuly named "get_record_by_id()"
	//=========================================================================
	//=========================================================================
	public static function get_record($table, $value, $field=false, $ds='')
	{
		$type = 'i';
		if (!$field) { $field = 'id'; }
		else if (is_array($field)) {
			$field = $field[0];
			if (count($field) > 1) { $type = $field[1]; }
		}
		$params = array($type, $value);
		$strsql = "select * from {$table} where {$field} = ?";
		return qdb_first_row($ds, $strsql, $params);
	}

	//=========================================================================
	//=========================================================================
	// Is Valid Record Function
	//=========================================================================
	// Previosuly named "is_valid_record()"
	//=========================================================================
	//=========================================================================
	public static function count_records($table, $fields, $ds='')
	{
		if (empty($fields) || !is_array($fields) || empty($table)) {
			return false;
		}
		$params = array('');
		$strsql = "select count(*) as count from {$table} where";
		foreach ($fields as $field_name => $field_data) {
			if ($params[0]) { $strsql .= ' and'; }
			$strsql .= " {$field_name} = ?";
			$params[0] .= $field_data[0];
			$params[] = $field_data[1];
		}
		return qdb_lookup($ds, $strsql, "count", $params);
	}

	//============================================================================
	//============================================================================
	// Delete Records Function
	//============================================================================
	//============================================================================
	public static function delete_records($table, $field, $value, $value_type='i', $ds='')
	{
		if (empty($field) || (string)$value === '') { return false; }
		$params = array($value_type, $value);
		$strsql = "delete from {$table} where {$field} = ?";
	
		return qdb_exec($ds, $strsql, $params);
	}

	//============================================================================
	//============================================================================
	// Get Tables with Field Function
	//============================================================================
	//============================================================================
	public static function get_tables_with_field($field, $ds='')
	{
		//-----------------------------------------------------
		// Determine Database
		//-----------------------------------------------------
		if ($ds == '') {
			if (isset($_SESSION['default_data_source'])) {
				$db = $_SESSION[$_SESSION['default_data_source']]['source'];
			}
			else {
				$scope = __CLASS__ . '::' . __METHOD__;
				if (function_exists('display_error')) {
					display_error($scope, 'Invalid or no data source given.');
				}
				else {
					tirgger_error('Invalid or no data source given.');
				}
			}
		}

		//-----------------------------------------------------
		// SQL Statement
		//-----------------------------------------------------
		$strsql = "
			SELECT 
				distinct(a.table_name) 
			FROM 
				information_schema.columns a, 
				information_schema.tables b 
			WHERE 
				a.table_schema = ? 
				and a.column_name = ? 
				and b.table_type = 'BASE TABLE' 
				and a.table_name = b.table_name
		";

		return qdb_exec($ds, $strsql, array('ss', $db, $field), 'table_name:table_name');
	}

	//============================================================================
	//============================================================================
	// Check Tables Function
	//============================================================================
	//============================================================================
	public static function check_tables($field, $val, $type='i', $tbls_to_ignore=false, $ds='', $return_format='simple')
	{
		if (!$field) { return false; }
		if ($val === false || $val === array()) { return false; }

		$ret_val = array();
		$tbls_to_check = self::get_tables_with_field($field, $ds);

		if ($tbls_to_check) {
			foreach ($tbls_to_check as $tbl) {

				//-----------------------------------------------------
				// Skip Table?
				//-----------------------------------------------------
				if (is_array($tbls_to_ignore) && in_array($tbl, $tbls_to_ignore)) { continue; }

				//-----------------------------------------------------
				// Multiple Values to Check
				//-----------------------------------------------------
				if (is_array($val)) {
					$params = array('');
					$field_vals = '';
					foreach ($val as $v) {
						$params[0] .= $type;
						$params[] = $v;
						if ($field_vals) { $field_vals .= ', '; }
						$field_vals .= '?';
					} 
					$strsql = "
						select 
							DISTINCT({$field}), 
							count({$field}) as count 
						from 
							{$tbl} 
						where 
							{$field} IN ({$field_vals}) 
						group by 
							{$field}
					";
				}
				//-----------------------------------------------------
				// Check Single Value
				//-----------------------------------------------------
				else {
					$strsql = "
						select 
							DISTINCT({$field}), 
							count({$field}) as count 
						from 
							{$tbl} 
						where 
							{$field} = ?
					";
					$params = array('i', $val);
				}
				if ($params[0] == '') { $params = []; }
				$ret_val[$tbl] = qdb_exec($ds, $strsql, $params);
			}
		}

		//---------------------------------------------------
		// Simplified Return Format
		//---------------------------------------------------
		if ($return_format == 'simple') {
			$new_ret_vals = array();
			foreach ($ret_val as $tbl) {
				foreach ($tbl as $rec) {
					if ($rec['count']) {
						if (!isset($new_ret_vals[$rec[$field]])) {
							$new_ret_vals[$rec[$field]] = 0;
						}
						$new_ret_vals[$rec[$field]] += $rec['count'];
					}
				}
			}
			return $new_ret_vals;
		}

		//---------------------------------------------------
		// Detailed Return Format
		//---------------------------------------------------
		return $ret_val;
	}

	//=========================================================================
	//=========================================================================
	// Make Bind Parameters Function
	//=========================================================================
	//=========================================================================
	public static function make_bind_parameters(&$params, $type, &$values)
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

	//=============================================================================
	//=============================================================================
	// Add SQL Parameter Function
	//=============================================================================
	//=============================================================================
	public static function add_sql_param(&$params, $field, $values, $type='i', $separator='and', $in=true)
	{
		//-----------------------------------------------------------
		// Checks
		//-----------------------------------------------------------
		if (!is_array($params) || (is_array($params) && count($params) == 0)) {
			$msg = "SQL parameters variable must be a non-empty array.";
			display_error(__FUNCTION__, $msg);
			return false;
		}
		if (empty($field) || !is_scalar($field)) {
			$msg = "SQL field name cannot be empty and must be a scalar value.";
			display_error(__FUNCTION__, $msg);
			return false;		
		}
	
		//-----------------------------------------------------------
		// Multiple Values
		//-----------------------------------------------------------
		if (is_array($values)) {
			$phrase = " {$separator} {$field} ";
			$phrase .= ($in) ? ('IN (') : ('NOT IN (');
			$count = 0;
			foreach ($values as $el) {
				if ($el == '') { continue; }
				$phrase .= ($count > 0) ? (', ?') : ('?');
				$params[0] .= $type;
				$params[] = $el;
				$count++;
			}
			$phrase .= ")";
			if (!$count) { return false; }
			return $phrase;
		}
		//-----------------------------------------------------------
		// Single Value
		//-----------------------------------------------------------
		else {
			$params[0] .= $type;
			$params[] = $values;
			return " {$separator} {$field} = ?";
		}
	
	}

}

