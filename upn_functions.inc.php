<?php
//*****************************************************************************
//*****************************************************************************
/**
* Register UPN Functions
*
* @package		phpOpenPlugins
* @subpackage	Global Functions
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 5/24/2016, Last updated: 5/24/2016
**/
//*****************************************************************************
//*****************************************************************************

include_once('POP_upn.class.php');

//=============================================================================
//=============================================================================
// Register _() and upn() Functions for use as UPN Shortcuts
//=============================================================================
//=============================================================================
if (!function_exists('__')) {
	function __() { return call_user_func_array(['POP_upn', '_'], func_get_args()); }
}
if (!function_exists('upn')) {
	function upn() { return call_user_func_array(['POP_upn', '_'], func_get_args()); }
}

