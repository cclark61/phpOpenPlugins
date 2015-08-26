<?php
//*****************************************************************************
//*****************************************************************************
/**
* MemCache Functions Plugin
*
* @package		phpOpenPlugins
* @subpackage	Database
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 8/25/2015, Last updated: 8/25/2015
**/
//*****************************************************************************
//*****************************************************************************
require_once('POP_static_core.class.php');

//*******************************************************************************
//*******************************************************************************
// POP MemCache Object
//*******************************************************************************
//*******************************************************************************
class POP_memcache extends POP_static_core
{

	//=============================================================================
	//=============================================================================
	/**
	* Get Data With Caching
	*
	* @param object A valid MemCached Object
	* @param string The cache key stub
	* @param mixed A function name or array of an object and the method to call
	* @param array An array of arguments to pass to the called function/method
	* @param integer The number of seconds the cached item should live
	* @param bool Cache the results? Default is yes. (True)
	*
	* @return string A unique cache key.
	*/
	//=============================================================================
	//=============================================================================
	public static function get_data_with_caching($m, $stub, $fn, $args, $ttl, $use_cache=true)
	{
		settype('int', $ttl);

		//-----------------------------------------------------------
		// Did we get a valid MemCached object?
		//-----------------------------------------------------------
		if (get_class($m) != 'Memcached') {
			self::display_error(__METHOD__, 'First parameter must be a valid Memcached object.');
			return false;
		}

		//-----------------------------------------------------------
		// Determine Function / Method
		// Update Cache Key Stub
		//-----------------------------------------------------------
		if (is_array($fn)) {
			$obj = $fn[0];
			$stub .= ":{$obj}";
			$fn = $fn[1];
		}
		$stub .= ":{$fn}";

		//-----------------------------------------------------------
		// Create Cache Key
		//-----------------------------------------------------------
		$cache_key = self::make_cache_key($stub, $args);

		//-----------------------------------------------------
		// Attempt to pull data from MemCache
		//-----------------------------------------------------
		$results = null;
		if ($use_cache) {
			$results = $m->get($cache_key);
		}

		//-----------------------------------------------------
		// Results Not Found / Don't Use MemCache
		//-----------------------------------------------------
		if (empty($results)) {

			$results = (!empty($obj)) ? ($obj->{$fn}($args)) : ({$fn}($args));

			//-----------------------------------------------------
			// Cache the Results in MemCache
			//-----------------------------------------------------
			if ($use_cache) {
				$m->set($cache_key, $results, time() + $ttl);
			}
		}

		return $results;		
	}

	//=============================================================================
	//=============================================================================
	/**
	* Create and return a cache key for use in MemCache for example.
	*
	* @param string Cache Key Stub
	* @param array The parameters passed to the original function.
	*
	* @return string A unique cache key.
	*/
	//=============================================================================
	//=============================================================================
	public static function make_cache_key($stub, $args)
	{
		if (empty($stub) || empty($args)) { return false; }
		$cache_key = $stub;

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

