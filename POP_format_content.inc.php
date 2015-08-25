<?php
//*****************************************************************************
//*****************************************************************************
/**
* Format Functions Plugin
*
* @package		phpOpenPlugins
* @subpackage	Format
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 7/17/2012, Last updated: 8/25/2015
**/
//*****************************************************************************
//*****************************************************************************

//=============================================================================
//=============================================================================
// Function to clean slashes from directory paths
//=============================================================================
//=============================================================================
function clean_dir(&$dir, $front_slashes=false, $rear_slashes=true)
{
	if (strlen($dir) > 0) {
		// Remove Trailing Slashes
		while ($rear_slashes && substr($dir, strlen($dir) - 1, 1) == "/") {
			$dir = substr($dir, 0, strlen($dir) - 1);
		}

		// Remove Front Slashes
		while ($front_slashes && substr($dir, 0, 1) == "/") {
			$dir = substr($dir, 1, strlen($dir));
		}
	}
}

//=============================================================================
//=============================================================================
// SQL Escape Values Function
//=============================================================================
//=============================================================================
function sql_escape_values(&$in_val, $ignore_indices=false)
{
	// Indices set to be ignored
	$tmp_ii = array();
	if (is_array($ignore_indices) && count($ignore_indices) > 0) {
		foreach ($ignore_indices as $key => $value) { $tmp_ii[$value] = $value; }
	}
	
	if (is_array($in_val)) {
		foreach ($in_val as $key => $val) {
			if (!isset($tmp_ii[$key])) { sql_escape_values($in_val[$key]); }
		}
	}
	else { $in_val = addslashes($in_val); }
}

//=============================================================================
//=============================================================================
// Set Category Function
//=============================================================================
//=============================================================================
function set_category(&$cat, $new_cat)
{
	if ($cat == "[+]" && $new_cat != "") { $cat = $new_cat; }
	else if ($cat == "[+]" && $new_cat == "") { $cat = ""; }
}

//=============================================================================
//=============================================================================
// Generic Date Format Function
//=============================================================================
//=============================================================================
function gen_format_date($stamp, $def_ret_val=false, $format="n/j/Y")
{
	$unix_stamp = strtotime($stamp);
	if ($unix_stamp > 0) { return date($format, $unix_stamp); }
	else { return $def_ret_val; }
}

//=============================================================================
//=============================================================================
// Convert Date to SQL Format Function
//=============================================================================
//=============================================================================
function format_date_sql($stamp, $def_ret_val=false, $format="Y-m-d")
{
	return gen_format_date($stamp, $def_ret_val, $format);
}

//=============================================================================
//=============================================================================
// Convert Date to Viewable Format Function
//=============================================================================
//=============================================================================
function format_date_pretty($stamp, $def_ret_val=false, $format="n/j/Y")
{
	return gen_format_date($stamp, $def_ret_val, $format);
}

//=============================================================================
//=============================================================================
// Print Code Function
//=============================================================================
//=============================================================================
function print_code($code, $return=false)
{
	if ($return) { ob_start(); }
	print div(nl2br($code), array("class" => "code_box"));
	if ($return) { return ob_get_clean(); }
}

//=============================================================================
//=============================================================================
// SEO Friendly String Function
//=============================================================================
//=============================================================================
function seo_friendly_str($str)
{
	$str = strtolower($str);
	$str = str_replace(' ' , '-', $str);
	$str = preg_replace("/[^a-zA-Z0-9_\-s]/", "", $str);
	return $str;
}

//=============================================================================
//=============================================================================
// Generate a globally unique identifier (GUID) Function
//=============================================================================
//=============================================================================
if (!function_exists('GUID')) {
	function GUID()
	{
	    if (function_exists('com_create_guid') === true) {
	        return trim(com_create_guid(), '{}');
	    }
	
	    return sprintf(
	    	'%04X%04X-%04X-%04X-%04X-%04X%04X%04X', 
	    	mt_rand(0, 65535), 
	    	mt_rand(0, 65535), 
	    	mt_rand(0, 65535), 
	    	mt_rand(16384, 20479), 
	    	mt_rand(32768, 49151), 
	    	mt_rand(0, 65535), 
	    	mt_rand(0, 65535), 
	    	mt_rand(0, 65535)
	    );
	}
}

//=============================================================================
//=============================================================================
// Load File Content Function
//=============================================================================
//=============================================================================
function load_file_content($dir, $file)
{
	$full_file = "{$dir}/{$file}";
	if (file_exists($full_file)) {
		ob_start();
		include($full_file);
		return ob_get_clean();
		
	}
	else { return false; }
}

//=============================================================================
//=============================================================================
// Sanitize and Escape for HTML Output Function
//=============================================================================
//=============================================================================
function html_sanitize($s)
{
	$s = preg_replace('/[^\xA|\xC|(\x20-\x7F)]*/','', (string)$s);
	return htmlspecialchars(strip_tags($s));
}

//=============================================================================
//=============================================================================
// Escape for HTML Output Function
//=============================================================================
//=============================================================================
function html_escape($s)
{
	$s = preg_replace('/[^\xA|\xC|(\x20-\x7F)]*/','', (string)$s);
	return htmlspecialchars($s);
}

//=============================================================================
//=============================================================================
// Fill If Empty Function
//=============================================================================
//=============================================================================
function fill_if_empty(&$data, $empty_val='--')
{
	$data = (trim((string)$data) == '') ? ($empty_val) : (trim($data));
}


//====================================================================
//====================================================================
// Add Message in Session Function
//====================================================================
//====================================================================
function add_message_session($msg, $type='gen')
{
	$index = strtolower("{$type}_message");
	if (isset($_SESSION[$index])) {
		if (!is_array($_SESSION[$index])) {
			$tmp = $_SESSION[$index];
			$_SESSION[$index] = array($tmp);
		}
	}
	else {
		$_SESSION[$index] = array();
	}
	$_SESSION[$index][] = (string)$msg;
	return true;
}

//====================================================================
//====================================================================
// Shortcut Message Functions
//====================================================================
//====================================================================
if (!function_exists('add_bottom_message')) {
	function add_bottom_message($msg) { add_message_session($msg, 'bottom'); }
}
if (!function_exists('add_page_message')) {
	function add_page_message($msg) { add_message_session($msg, 'page'); }
}
if (!function_exists('add_action_message')) {
	function add_action_message($msg) { add_message_session($msg, 'action'); }
}
if (!function_exists('add_warn_message')) {
	function add_warn_message($msg) { add_message_session($msg, 'warn'); }
}
if (!function_exists('add_error_message')) {
	function add_error_message($msg) { add_message_session($msg, 'error'); }
}
if (!function_exists('add_gen_message')) {
	function add_gen_message($msg) { add_message_session($msg, 'gen'); }
}
if (!function_exists('add_timer_message')) {
	function add_timer_message($msg) { add_message_session($msg, 'timer'); }
}

//====================================================================
//====================================================================
// Add Message in Local Variable Function
//====================================================================
//====================================================================
function add_message_local($msg, &$var)
{
	if (!is_array($var)) {
		$tmp = $var;
		$var = array($tmp);
	}
	$var[] = (string)$msg;
	return true;
}


//=============================================================================
//=============================================================================
// Format Records Function
//=============================================================================
//=============================================================================
function format_records(&$recs, $fields)
{
	if (!is_array($fields)) {
		$msg = 'Second parameter must be an array of key/value pairs that specify the field name and the formatting function name respectively.';
		trigger_error(__FUNCTION__ . "() :: {$msg}");
		return false;
	}
	else if (!$recs) { return false; }
	else if (!$fields) { return false; }

	//------------------------------------------------------------
	// Process Records
	//------------------------------------------------------------
	$processed = 0;
	foreach ($recs as &$rec) {
		if (!is_array($rec)) { continue; }
		foreach ($fields as $field => $fn) {
			if (isset($rec[$field])) {
				if (is_array($fn)) {
					$sub_procs = 0;
					foreach ($fn as $sub_fn) {
						if (function_exists($sub_fn)) {
							$rec[$field] = call_user_func($sub_fn, $rec[$field]);
							$sub_procs++;
						}
					}
					if ($sub_procs) { $processed++; }
				}
				else {
					if (is_callable($fn)) {
						$rec[$field] = $fn($rec[$field]);
						$processed++;
					}
					else if (function_exists($fn)) {
						$rec[$field] = call_user_func($fn, $rec[$field]);
						$processed++;					
					}
				}
			}
		}
	}

	return $processed;
}

//=============================================================================
//=============================================================================
// Display Error Function
//=============================================================================
//=============================================================================
if (!function_exists('display_error')) {
	function display_error($scope, $error_msg, $error_type=E_USER_NOTICE)
	{
		$tmp_msg = "Error :: {$scope}() - {$error_msg}";
		return trigger_error($tmp_msg, $error_type);
	}
}

//=============================================================================
//=============================================================================
// Format Filesize Function
//=============================================================================
//=============================================================================
function format_filesize($bytes)
{
    if ($bytes < 1024) {
        return $bytes .' B';
    }
    elseif ($bytes < 1048576) {
        return round($bytes / 1024, 2) .' KB';
    }
    elseif ($bytes < 1073741824) {
        return round($bytes / 1048576, 2) . ' MB';
    }
    else {
        return round($bytes / 1073741824, 2) . ' GB';
    }
}

//=============================================================================
//=============================================================================
// Get Saveable Password Function
//=============================================================================
//=============================================================================
function get_saveable_password($pass, $aps=false)
{
	if (!$aps && isset($_SESSION['auth_pass_security'])) {
		$aps = strtolower($_SESSION['auth_pass_security']);
	}

	if ($aps) {
		switch ($aps) {

			case 'sha1':
				return sha1($pass);
				break;

			case 'sha256':
				return hash('sha256', $pass);
				break;

			case 'md5':
				return md5($pass);
				break;
		}
	}

	return $pass;
}

//=============================================================================
//=============================================================================
/**
* Create and return a cache key for use in MemCache for example.
*
* @param string Function Name
* @param array The parameters passed to the original function.
*
* @return string A unique cache key.
*/
//=============================================================================
//=============================================================================
if (!function_exists('make_cache_key')) {
	function make_cache_key($fn, $args)
	{
		if (empty($fn) || empty($args)) { return false; }

		//-----------------------------------------------------
		// Build Cache Key
		//-----------------------------------------------------
		$cache_key = '';
		if (isset($_SESSION['app_code'])) { $cache_key .= "{$_SESSION['app_code']}:"; }
		if (isset($_SESSION['app_key'])) { $cache_key .= "{$_SESSION['app_key']}:"; }
		if (isset($_SESSION['ENV'])) { $cache_key .= "{$_SESSION['ENV']}:";	}
		$cache_key .= $fn;

		if (is_array($args)) {
			foreach ($args as $arg) {
				if (is_array($arg)) {
					$cache_key .= ':' . serialize($arg);
				}
				else {
					$cache_key .= ":{$arg}";
				}
			}
		}
		else {
			$cache_key .= ":{$args}";
		}
	
		$cache_key = md5($cache_key);
	
		return $cache_key;
	}
}

//=============================================================================
//=============================================================================
/**
 * This method will redirect the user to the given page and also send
 * a message with it if wanted
 *
 * @param string $location The location to send the user, if empty $_SERVER['REDIRECT_URL'] is used
 * @param string $message. The message to display once redirected
 * @param mixed $message_type The message type. Options are:
 * 		'error_message', 'warn_message', 'action_message' (default), 'gen_message', 'page_message'
 */
//=============================================================================
//=============================================================================
if (!function_exists('redirect')) {
	function redirect($location=false, $message=false, $message_type='action_message')
	{
		//-----------------------------------------------------
		// Set flag to stop page render
		//-----------------------------------------------------
		define('POFW_SKIP_RENDER', 1);

		//-----------------------------------------------------
		// Set the location
		//-----------------------------------------------------
		if (empty($location)) {
			$qs_start = strpos($_SERVER['REQUEST_URI'], '?');
			if ($qs_start === false) {
				$location = $_SERVER['REQUEST_URI'];
			}
			else {
				$location = substr($_SERVER['REQUEST_URI'], 0, $qs_start);
			}
		}

		//-----------------------------------------------------
		// Add a Message?
		//-----------------------------------------------------
		$msg_func = 'add_' . $message_type;
		if (!empty($message) && function_exists($msg_func)) {
			call_user_func($msg_func, $message);
		}
	
		//-----------------------------------------------------
		// Redirect
		//-----------------------------------------------------
		header("Location: {$location}");
		exit;
	}
}

//=========================================================================
//=========================================================================
/**
* Return a CSS based Icon
*
* @param string Icon to use i.e. 'fa fa-check'
*
* @return string HTML CSS Icon
*/
//=========================================================================
//=========================================================================
function css_icon($i)
{
	if (empty($i)) { return false; }
	return "<i class=\"{$i}\"></i>";
}

