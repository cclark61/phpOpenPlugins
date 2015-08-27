<?php
//*****************************************************************************
//*****************************************************************************
/**
* Database Interface Object Functions Plugin
*
* @package		phpOpenPlugins
* @subpackage	Database
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 8/25/2015, Last updated: 8/27/2015
**/
//*****************************************************************************
//*****************************************************************************

//*******************************************************************************
//*******************************************************************************
// POP DIO Object
//*******************************************************************************
//*******************************************************************************
class POP_dio
{

	//=============================================================================
	//=============================================================================
	// Set DIO Field to NULL
	//=============================================================================
	//=============================================================================
	public static function set_field_null(&$obj, $field_name)
	{
		$obj->set_field_quotes($field_name, 'disable');
		$obj->set_field_data($field_name, 'NULL');
		$obj->set_use_bind_param($field_name, false);
	}

	//=============================================================================
	//=============================================================================
	// Set DIO Field to Current Date/Time
	//=============================================================================
	//=============================================================================
	public static function set_field_current_dttm(&$obj, $field_name)
	{
		$obj->set_field_quotes($field_name, 'disable');
		$obj->set_field_data($field_name, 'NOW()');
		$obj->set_use_bind_param($field_name, false);
	}

	//=============================================================================
	//=============================================================================
	// Save DIO Record Function
	//=============================================================================
	//=============================================================================
	public static function save_rec($plugin, $obj_name, $data, $pkey=false, $args=false)
	{
		//------------------------------------------------------
		// Transaction Type
		//------------------------------------------------------
		$trans_type = (empty($pkey)) ? ('add') : ('update');
	
		//------------------------------------------------------
		// Validate that Data is an array
		//------------------------------------------------------
		if (!is_array($data)) { return false; }
	
		//------------------------------------------------------
		// Optional Parameters / Arguments
		//------------------------------------------------------
		if (is_array($args)) { extract($args); }
	
		//------------------------------------------------------
		// Load Plugin
		//------------------------------------------------------
		if (!empty($plugin)) { load_plugin($plugin); }
	
		//------------------------------------------------------
		// Create Object
		//------------------------------------------------------
		$o = new $obj_name();
	
		//------------------------------------------------------
		// If a Primary Key was passed...
		//------------------------------------------------------
		if ($pkey) {
			$load_status = $o->load($pkey);
			if ($load_status != 1) {
				if (empty($add_if_no_exist)) { return false; }
				else { $trans_type = 'add'; }
			}
		}
	
		//------------------------------------------------------
		// Import Data
		//------------------------------------------------------
		$o->import($data);

		//------------------------------------------------------
		// Set Account ID?
		//------------------------------------------------------
		//if (!isset($data['account_id']) && !empty($_SESSION['account_id'])) {
			//$o->set_field_data('account_id', $_SESSION['account_id']);
		//}
	
		//------------------------------------------------------
		// Do not save field "id" unless explicitly
		// told to do so
		//------------------------------------------------------
		if (empty($save_id)) { $o->no_save("id"); }
	
		//------------------------------------------------------
		// Print Only (Debug)
		//------------------------------------------------------
		if (!empty($print_only)) { $o->print_only(); }
	
		//------------------------------------------------------
		// Save
		//------------------------------------------------------
		$save_val = $o->save($pkey, false, $pkey);
		if (!empty($print_only)) { print $save_val; }
	
		return array(
			'trans_type' => $trans_type,
			'save_val' => $save_val
		);
	}
	
}


