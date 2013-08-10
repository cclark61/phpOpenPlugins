<?php
//*****************************************************************************
//*****************************************************************************
/**
* Data Cache Objects
*
* @package		phpOpenPlugins
* @subpackage	Utilities
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 1/28/2012, Last updated: 2/23/2012
**/
//*****************************************************************************
//*****************************************************************************

//*******************************************************************************
//*******************************************************************************
// Local Level Data Cache Object
//*******************************************************************************
//*******************************************************************************
class LDC extends DCO
{

	//*************************************************************************
	// Constructor Function
	//*************************************************************************
    public function __construct()
    {
        $this->container = array();
        $this->scope = 'local';
		$this->existed = false;
    }

}

//*******************************************************************************
//*******************************************************************************
// Global Level Data Cache Object
//*******************************************************************************
//*******************************************************************************
class GDC extends DCO
{

	//*************************************************************************
	// Constructor Function
	//*************************************************************************
    public function __construct($key)
    {
    	if (!$key) {
    		trigger_error('You must specify a valid cache key to be used as a cache reference.');
    		return false;
    	}
    	$this->key = (string)$key;
    	$this->existed = true;
    	if (!isset($GLOBALS['dco'][$this->key])) {
    		$GLOBALS['dco'][$this->key] = array();
	    	$this->existed = false;
    	}
        $this->container =& $GLOBALS['dco'][$this->key];
        $this->scope = 'global';
    }

}

//*******************************************************************************
//*******************************************************************************
// Session Level Data Cache Object
//*******************************************************************************
//*******************************************************************************
class SDC extends DCO
{

	//*************************************************************************
	// Constructor Function
	//*************************************************************************
    public function __construct($key)
    {
    	if (!$key) {
    		trigger_error('You must specify a valid cache key to be used as a cache reference.');
    		return false;
    	}
    	$this->key = (string)$key;
    	$this->existed = true;
    	if (!isset($_SESSION['dco'][$this->key])) {
    		$_SESSION['dco'][$this->key] = array();
    		$this->existed = false;
    	}
        $this->container =& $_SESSION['dco'][$this->key];
        $this->scope = 'session';
    }

}

//*******************************************************************************
//*******************************************************************************
// Data Cache Object (abstract)
//*******************************************************************************
//*******************************************************************************
abstract class DCO
{
	//*************************************************************************
	// Class Variables
	//*************************************************************************

	protected $key;
	protected $container;
	protected $scope;
	protected $existed;

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
    	print "<pre>\n";
    	if ($this->key) { print "Key: '{$this->key}'"; }
    	print "\nData: ";
    	print_r($this->container);
    	print "</pre>\n";	
    	return ob_get_clean() . "<br/>\n";
    }	   

	//*************************************************************************
	// Clear Data Function
	//*************************************************************************
	public function clear_data() { $this->container = array(); }

	//*************************************************************************
	// Set Data Function
	//*************************************************************************
	public function set($key, $data, $overwrite=true)
	{
		if (isset($this->container[$key])) {
			if ($overwrite) { $this->container[$key] = $data; }
			else { return false; }
		}
		else { $this->container[$key] = $data; }
		return true;
	}

	//*************************************************************************
	// Get Data Function
	//*************************************************************************
	public function get($key, $return_ref=false)
	{
		return (isset($this->container[$key])) ? ($this->container[$key]) : (false);
	}

	//*************************************************************************
	// Delete Data Function
	//*************************************************************************
	public function delete($key)
	{
		if (isset($this->container[$key])) {
			unset($this->container[$key]);
			return true;
		}
		return false;
	}

	//*************************************************************************
	// Getter Functions
	//*************************************************************************
	public function scope() { return $this->scope; }
	public function existed() { return $this->existed; }

}

?>
