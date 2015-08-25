<?php
//*****************************************************************************
//*****************************************************************************
/**
* Form Filters Plugin
*
* @package		phpOpenPlugins
* @subpackage	Forms
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 2/19/2007, Last updated: 4/1/2013
**/
//*****************************************************************************
//*****************************************************************************

//=============================================================================
//=============================================================================
// Save and Return Filter Data Function
//=============================================================================
//=============================================================================
function save_and_return_filter_data(&$current_page, $request_index, $cookie_index, $default)
{
	$in_var = (isset($_REQUEST[$request_index])) ? ($_REQUEST[$request_index]) : (null);
	
	if (is_null($in_var)) {
		$in_var = $current_page->get_mod_var($cookie_index);
		if ($in_var === false) {
			if (isset($_COOKIE[$cookie_index])) { $in_var = $_COOKIE[$cookie_index]; }
			else { $in_var = $default; }
		}
	}
	else {
		$current_page->set_mod_var($cookie_index, $in_var);
		setcookie($cookie_index, $in_var, time() + 604800);
	}
	
	return $in_var;
}

//=============================================================================
//=============================================================================
// Create Select Filter Function
//=============================================================================
//=============================================================================
function create_select_filter($select_vals, $url_stub, $selected="-1", $label="")
{	
	// Label
	if ((string)$label !== "") { print xhe("label", $label); }
	
	// Select
	ob_start();
	foreach ($select_vals as $key => $display) {
		$url = $url_stub . $key;
		$o_attrs = array("value" => $url);
		if ($selected == $key) { $o_attrs["selected"] = "selected"; }
		print xhe("option", $display, $o_attrs);
	}
	
	print xhe("select", ob_get_clean(), array("class" => "filter"));	
}

//=============================================================================
//=============================================================================
// Print a List Filter Select Dropdown Function
//=============================================================================
//=============================================================================
function print_list_filter(&$page, $base_url, $options, $get_var, $cookie_mod_var, $label)
{
	$ret_vals = array();
	if (isset($_GET[$get_var]) && $_GET[$get_var] != '') { $$get_var = $_GET[$get_var]; }

	if (!isset($$get_var)) {
		$$get_var = $page->get_mod_var($cookie_mod_var);
		if ($$get_var === false) {
			if (isset($_COOKIE[$cookie_mod_var])) { $$get_var = $_COOKIE[$cookie_mod_var]; }
			else { $$get_var = 0; }
		}
	}
	else {
		$page->set_mod_var($cookie_mod_var, $$get_var);
		setcookie($cookie_mod_var, $$get_var, time() + 604800);
	}

	ob_start();
	print xhe('label', $label) . "\n";
	ob_start();
	foreach ($options as $key => $option) {
		$url = add_url_params($base_url, array($get_var => $key));
		$o_attrs = array("value" => $url);
		if (isset($$get_var) && $$get_var == $key) {
			$o_attrs["selected"] = "selected";
			if (isset($option["group_by"])) { $ret_vals['group_by'] = $option["group_by"]; }
		}
		print xhe("option", $option["display"], $o_attrs);
	}
	
	print xhe("select", ob_get_clean(), array("class" => "filter"));
	print xhe('span', ob_get_clean(), array('class' => 'filter_wrapper'));

	$ret_vals[$get_var] = $$get_var;
	return $ret_vals;
}

