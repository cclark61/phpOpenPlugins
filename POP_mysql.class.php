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
require_once('POP_static_core.class.php');

//*******************************************************************************
//*******************************************************************************
// POP MySQL Object
//*******************************************************************************
//*******************************************************************************
class POP_mysql extends POP_static_core
{

	//#########################################################################
	//=========================================================================
	//=========================================================================
	// Parameters / Usage for functions:
	// -> get_record()
	// -> get_records()
	// -> count_records()
	// -> delete_records()
	//=========================================================================
	// * Parameter #1: Table or [Data Source, Table]
	// 
	//=========================================================================
	//=========================================================================
	//#########################################################################

	//=========================================================================
	//=========================================================================
	// Get Record Function
	//=========================================================================
	// Previosuly named "get_record_by_id()"
	//=========================================================================
	//=========================================================================
	public static function get_record($table, $fields_values, $args=false)
	{
		$sql_parts = self::build_sql_parts(func_get_args())
		if (!$sql_parts) { return null; }
		else { extract($sql_parts); }
		$strsql = "select {$columns} from {$table} where {$where}";
		if (!empty($group_by)) { $strsql .= ' ' . $group_by; }
		if (!empty($order_by)) { $strsql .= ' ' . $order_by; }
		$strsql .= ' limit 1';
		return qdb_first_row($ds, $strsql, $params);
	}

	//=========================================================================
	//=========================================================================
	// Get Records Function
	//=========================================================================
	//=========================================================================
	public static function get_records($table, $fields_values, $args=false)
	{
		$sql_parts = self::build_sql_parts(func_get_args())
		if (!$sql_parts) { return null; }
		else { extract($sql_parts); }
		$strsql = "select {$columns} from {$table} where {$where}";
		if (!empty($group_by)) { $strsql .= ' ' . $group_by; }
		if (!empty($order_by)) { $strsql .= ' ' . $order_by; }
		if (!empty($limit)) { $strsql .= ' ' . $limit; }
		return qdb_exec($ds, $strsql, $params);
	}

	//=========================================================================
	//=========================================================================
	// Is Valid Record Function
	//=========================================================================
	// Previosuly named "is_valid_record()"
	//=========================================================================
	//=========================================================================
	public static function count_records($table, $fields_values, $args=false)
	{
		$sql_parts = self::build_sql_parts(func_get_args())
		if (!$sql_parts) { return null; }
		else { extract($sql_parts); }
		$strsql = "select count(*) as count from {$table} where {$where}";
		if (!empty($group_by)) { $strsql .= ' ' . $group_by; }
		if (!empty($order_by)) { $strsql .= ' ' . $order_by; }
		if (!empty($limit)) { $strsql .= ' ' . $limit; }
		return qdb_lookup($ds, $strsql, "count", $params);
	}

	//============================================================================
	//============================================================================
	// Delete Records Function
	//============================================================================
	//============================================================================
	public static function delete_records($table, $fields_values, $args=false)
	{
		$sql_parts = self::build_sql_parts(func_get_args())
		if (!$sql_parts) { return null; }
		else { extract($sql_parts); }
		$strsql = "delete from {$table} where {$where}";
		if (!empty($limit)) { $strsql .= ' ' . $limit; }
		return qdb_exec($ds, $strsql, $params);
	}

	//============================================================================
	//============================================================================
	// Delete Record Function
	//============================================================================
	//============================================================================
	public static function delete_record($table, $fields_values, $args=false)
	{
		if (!is_array($args)) { $args = []; }
		$args['limit'] = 1;
		return self::delete_records($table, $fields_values, $args);
	}

	//============================================================================
	//============================================================================
	// Build Where Clause Function
	//============================================================================
	//============================================================================
	public static function build_sql_parts()
	{
		//------------------------------------------------------------------
		// Validation / Defaults / Setup
		//------------------------------------------------------------------
		$args = func_get_args();
		if (!$args[0] || !$args[1]) { return false; }
		$ret_vals = [];
		$columns = '*';
		$params = [];

		//------------------------------------------------------------------
		// Parameter #3: Passed in arguments.
		// Use as the base for return values.
		//------------------------------------------------------------------
		if (!empty($args[2]) && is_array($args[2])) {
			$ret_vals = $args[2];
		}

		//------------------------------------------------------------------
		// Parameter #1: Table or [Data Source, Table]
		//------------------------------------------------------------------
		if (is_array($args[0])) {
			if (count($args[0]) > 1) {
				$ds = $args[0][0];
				$table = $args[0][1];
			}
			else { $table = $args[0][0]; }
		}
		else { $table = $args[0]; }

		//------------------------------------------------------------------
		// Parameter #2: Fields / Values
		//------------------------------------------------------------------
		if (is_array($args[1])) {

		}
		else {
			
		}

		//------------------------------------------------------------------
		// Build Where / Params
		//------------------------------------------------------------------

		//------------------------------------------------------------------
		// Add Values to Return Values Array
		//------------------------------------------------------------------
		$ret_vals['ds'] = $ds;
		$ret_vals['table'] = $table;
		$ret_vals['columns'] = $columns;
		$ret_vals['params'] = $params;

		return $ret_vals;
	}

	//============================================================================
	//============================================================================
	// Get Tables with Field Function
	//============================================================================
	//============================================================================
	public static function get_tables_with_field($field, $ds='', $db=false)
	{
		//-----------------------------------------------------
		// Determine Database
		//-----------------------------------------------------
		if ($db == '') {
			if ($ds == '') {
				if (!empty($_SESSION['default_data_source'])) {
					$ds = $_SESSION['default_data_source'];
				}
				else {
					self::display_error(__METHOD__, 'Invalid or no data source given. (1)');
					return false;					
				}
			}
			if (!empty($_SESSION[$ds]['source'])) {
				$db = $_SESSION[$ds]['source'];
			}
			else {
				self::display_error(__METHOD__, 'Invalid or no data source given. (2)');
				return false;
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
