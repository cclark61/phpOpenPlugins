<?php
//*****************************************************************************
//*****************************************************************************
/**
* A Core plugin for Classes used with static functions
*
* @package		phpOpenPlugins
* @subpackage	Core
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 8/25/2015, Last updated: 8/25/2015
**/
//*****************************************************************************
//*****************************************************************************
abstract class POP_static_core
{
	//========================================================================
	/**
	* Display Error Function
	**/
	//========================================================================
	protected static function display_error($function, $msg)
	{
		trigger_error("Error: {$function}(): {$msg}");
	}

}
