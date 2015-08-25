<?php
//*****************************************************************************
//*****************************************************************************
/**
* Twitter Bootstrap Object (for TB 2.x)
*
* @package		phpOpenPlugins
* @subpackage	Twitter Bootstrap 2.x
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 1/30/2013, Last updated: 8/24/2015
**/
//*****************************************************************************
//*****************************************************************************

//*******************************************************************************
//*******************************************************************************
// Twitter Bootstrap Object
//*******************************************************************************
//*******************************************************************************
class POP_TB
{

	//====================================================================
	//====================================================================
	// Control Group
	//====================================================================
	//====================================================================
	public static function control_group($content, $attrs=false)
	{
		if (!is_array($attrs)) { $attrs = array(); }
		if (isset($attrs['class'])) { $attrs['class'] .= ' control-group'; }
		else { $attrs['class'] = 'control-group'; }
		return div($content, $attrs);
	}

	//====================================================================
	//====================================================================
	// Control Label
	//====================================================================
	//====================================================================
	public static function control_label($content, $attrs=false)
	{
		if (!is_array($attrs)) { $attrs = array(); }
		if (isset($attrs['class'])) { $attrs['class'] .= ' control-label'; }
		else { $attrs['class'] = 'control-label'; }
		return label($content, $attrs);
	}

	//====================================================================
	//====================================================================
	// Controls
	//====================================================================
	//====================================================================
	public static function controls($content, $attrs=false)
	{
		if (!is_array($attrs)) { $attrs = array(); }
		if (isset($attrs['class'])) { $attrs['class'] .= ' controls'; }
		else { $attrs['class'] = 'controls'; }
		return div($content, $attrs);
	}

	//====================================================================
	//====================================================================
	// Checkbox Label
	//====================================================================
	//====================================================================
	public static function checkbox_label($content, $attrs=false)
	{
		if (!is_array($attrs)) { $attrs = array(); }
		if (isset($attrs['class'])) { $attrs['class'] .= ' checkbox'; }
		else { $attrs['class'] = 'checkbox'; }
		return label($content, $attrs);
	}

	//====================================================================
	//====================================================================
	// Save Button
	//====================================================================
	//====================================================================
	public static function save_button($button_text=false, $attrs=false)
	{
		//---------------------------------------------
		// Button Text
		//---------------------------------------------
		if ((string)$button_text == '') { $button_text ='Save'; }

		//---------------------------------------------
		// Attributes
		//---------------------------------------------
		if (!is_array($attrs)) { $attrs = array(); }
		if (isset($attrs['class'])) { $attrs['class'] = 'btn ' . $attrs['class']; }
		else { $attrs['class'] = 'btn btn-primary'; }
		$attrs['type'] = 'submit';

		//---------------------------------------------
		// Build and Return
		//---------------------------------------------
		return self::control_group(
			self::controls(
				button($button_text, $attrs)
			)
		);

	}

	//====================================================================
	//====================================================================
	// Simple Control Group
	//====================================================================
	//====================================================================
	public static function simple_control_group($label=false, $controls=false, $attrs=false, $attrs2=false)
	{
		ob_start();

		//---------------------------------------------
		// Control Label
		//---------------------------------------------
		if ($label != '') {
			print self::control_label($label, $attrs);
		}
		
		if ($controls) {
			if (is_array($controls)) { $controls = implode('', $controls); }
			print self::controls($controls);
		}
		
		return self::control_group(ob_get_clean(), $attrs2);
	}

	//====================================================================
	//====================================================================
	// Delete Form Function
	//====================================================================
	//====================================================================
	public static function delete_form($page, $args=false)
	{
		if (is_array($args)) { extract($args); }
		if (!isset($url)) { $url = false; }
		if (!isset($message)) {
			$message = 'Are you sure you want to delete this record?';
		}
	
		//---------------------------------
		// Create Form
		//---------------------------------
		$form = new form_too($url);
		$page->clear_mod_var("form_key");
		$page->set_mod_var("form_key", $form->use_key());
	
		//---------------------------------
		// Form Label
		//---------------------------------
		if (!empty($form_label)) { $form->label((string)$form_label); }
		
		//---------------------------------
		// Message
		//---------------------------------
		$form->add_element(xhe("h4", $message, array("class" => "del_form_message")));
	
		//---------------------------------
		// Hidden Variables
		//---------------------------------
		if (isset($hidden_vars) && is_array($hidden_vars)) {
			foreach ($hidden_vars as $key => $val) { $form->add_hidden($key, $val); }
		}
	
		//---------------------------------
		// Buttons
		//---------------------------------
		$form->start_div(array('class' => 'control-group'));
		$form->start_div(array('class' => 'controls'));
	
		$form->add_element(button('Cancel', array('name' => 'button_0', 'type' => "submit", 'class' => "btn", 'value' => 'Cancel')));
		$form->add_element(button('Delete', array('name' => 'button_1', 'type' => "submit", 'class' => "btn btn-danger", 'value' => 'Delete')));
		
		$form->end_div();
		$form->end_div();
	
		//---------------------------------
		// Render Form
		//---------------------------------
		$form->render();
	}

	//=========================================================================
	//=========================================================================
	/**
	* Return a Bootstrap "Label"
	*
	* @param string Label Contents
	* @param string the Bootstrap Label class AFTER the dash. I.e. 'success', 'info', etc.
	*
	* @return string HTML CSS Icon
	*/
	//=========================================================================
	//=========================================================================
	public static function label($contents, $type=false)
	{
		if ($contents == '') { return false; }
		if (empty($type)) { $type == 'default'; }
		if (!is_array($attrs)) { $attrs = array(); }
		$attrs['class'] = (!empty($attrs['class'])) ? ($attrs['class'] . ' label label-' . $type) : ('label label-' . $type);
		return xhe('span', $contents, $attrs);
	}

	//=========================================================================
	//=========================================================================
	/**
	* Return a Bootstrap "Badge"
	*
	* @param string Badge Contents
	* @param string the Bootstrap Badge class AFTER the dash. I.e. 'success', 'info', etc.
	*
	* @return string HTML CSS Icon
	*/
	//=========================================================================
	//=========================================================================
	public static function badge($contents, $type=false)
	{
		if ($contents == '') { return false; }
		if (empty($type)) { $type == 'default'; }
		if (!is_array($attrs)) { $attrs = array(); }
		$attrs['class'] = (!empty($attrs['class'])) ? ($attrs['class'] . ' badge badge-' . $type) : ('badge badge-' . $type);
		return xhe('span', $contents, $attrs);
	}

	//=========================================================================
	//=========================================================================
	/**
	* Return a Bootstrap "Alert"
	*
	* @param string Alert Contents
	* @param string the Bootstrap Badge class AFTER the dash. I.e. 'success', 'info', etc.
	*
	* @return string HTML CSS Icon
	*/
	//=========================================================================
	//=========================================================================
	public static function alert($contents, $type=false)
	{
		if ($contents == '') { return false; }
		if (empty($type)) { $type == 'info'; }
		if (!is_array($attrs)) { $attrs = array(); }
		$attrs['class'] = (!empty($attrs['class'])) ? ($attrs['class'] . ' alert alert-' . $type) : ('alert alert-' . $type);
		return xhe('div', $contents, $attrs);
	}
}

