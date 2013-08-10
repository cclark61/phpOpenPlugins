<?php
//*****************************************************************************
//*****************************************************************************
/**
* Content Template Object
*
* @package		phpOpenPlugins
* @subpackage	Content
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 2/4/2012, Last updated: 2/18/2013
**/
//*****************************************************************************
//*****************************************************************************

//*******************************************************************************
//*******************************************************************************
// Content Template Object
//*******************************************************************************
//*******************************************************************************
class POP_content_template
{

	//*************************************************************************
	// Class Variables
	//*************************************************************************

	protected $root_node;
	protected $template;
	protected $data;

	//*************************************************************************
	// Constructor Function
	//*************************************************************************
    public function __construct($root_node, $template=false)
    {
		$this->set_root_node($root_node);
        $this->data = array();
        if ($template) { $this->set_template($template); }
        $this->show_data_only = false;
    }

	//*************************************************************************
	// Destructor Function
	//*************************************************************************
	public function __destruct() {}

	//*************************************************************************
	// Object Conversion to String Function
	//*************************************************************************
    public function __toString()
    {
    	ob_start();
    	$this->render();	
    	return ob_get_clean();
    }	   

	//*************************************************************************
	// Display Error Function
	//*************************************************************************
    protected function display_error($function, $error_msg)
    {
    	$tmp_msg = 'Class [' . __CLASS__ . "]::{$function}() - ";
    	$tmp_msg .= "Error: {$error_msg}";
    	trigger_error($tmp_msg);
    }

	//*************************************************************************
	// Set Root Node Function
	//*************************************************************************
	public function set_root_node($root_node)
	{
		$new_rn = (string)$root_node;
		if ($new_rn == '' || is_numeric($new_rn)) {
			$msg = "Invalid root node name '{$new_rn}'. Root node name must be a string and not entirely numeric.";
			$this->display_error(__FUNCTION__, $msg);
			return false;
		}
		$this->root_node = $new_rn;
		return true;
	}

	//*************************************************************************
	// Set Template Function
	//*************************************************************************
	public function set_template($template)
	{
		$new_temp = (string)$template;
		if (!file_exists($new_temp)) {
			$msg = "Invalid template. File '{$new_temp}' does not exist.";
			$this->display_error(__FUNCTION__, $msg);
			return false;
		}
		$this->template = $new_temp;
		return true;
	}

	//*************************************************************************
	// Clear Data Function
	//*************************************************************************
	public function clear_data() { $this->data = array(); }

	//*************************************************************************
	// Set Data Function
	//*************************************************************************
	public function set($key, $data, $overwrite=true)
	{
		if (isset($this->data[$key])) {
			if ($overwrite) { $this->data[$key] = $data; }
			else { return false; }
		}
		else { $this->data[$key] = $data; }
		return true;
	}

	//*************************************************************************
	// Get Data Function
	//*************************************************************************
	public function get($key)
	{
		return (isset($this->data[$key])) ? ($this->data[$key]) : (false);
	}

	//*************************************************************************
	// Delete Data Function
	//*************************************************************************
	public function delete($key)
	{
		if (isset($this->data[$key])) {
			unset($this->data[$key]);
			return true;
		}
		return false;
	}

	//*************************************************************************
	// Set Show Data Only Function
	//*************************************************************************
	public function set_show_data_only($flag=true) { $this->show_data_only = (bool)$flag; }

	//*************************************************************************
	// Render Function
	//*************************************************************************
	public function render()
	{
		$xml = array2xml($this->root_node, xml_escape_array($this->data));
		if ($this->show_data_only) {
			print $xml;
			return true;
		}
		else if ($this->template) {
			xml_transform($xml, $this->template);
			return true;
		}
		return false;
	}
}

?>
