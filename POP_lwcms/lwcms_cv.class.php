<?php
//**************************************************************************
/**
* LWCMS Content Version Plugin
*
* @package		phpOpenPlugins
* @subpackage	LWCMS
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 2/28/2013, Last updated: 3/5/2013
**/
//**************************************************************************

class LWCMS_CV
{

	//******************************************************************
	// Get Version Content Function (i.e. from database)
	//******************************************************************
	public static function get_version_content($ds, $ver_id=0)
	{
		if (!$ver_id) { return false; }

		//-------------------------------------------------
		// Pull Version Content
		//-------------------------------------------------
		$strsql = "select raw_content from content_versions where id = ?";
		$versions = qdb_exec($ds, $strsql, array('i', $ver_id));
		
		if (isset($versions[0])) {
			return $versions[0]['raw_content'];
		}
		else { return false; }
	}

	//******************************************************************
	// Get Cached Version Content Function
	//******************************************************************
	public static function get_cached_version_content($full_cache_file=false)
	{
		if (!$full_cache_file) { return false; }
		else {
			if (is_readable($full_cache_file)) {
				$handle = fopen($full_cache_file, "r");
				$file_content = fread($handle, filesize($full_cache_file));
				fclose($handle);
				if ($file_content) { return $file_content; }
				else { return false; }
			}
			else { return false; }
		}
	}

	//******************************************************************
	// Set Cached Version Content Function (i.e. save it to file)
	//******************************************************************
	public static function set_cached_version_content(&$file_content, &$full_cache_path, &$full_cache_file)
	{
		//-------------------------------------------------
		// Check parameters
		//-------------------------------------------------
		if (!$file_content || !$full_cache_path || !$full_cache_file) {
			return false;
		}

		//-------------------------------------------------
		// Full cache directory exists
		//-------------------------------------------------
		if (is_dir($full_cache_path)) {
			if (is_writable($full_cache_path)) {
				$handle = fopen($full_cache_file, 'w');
				$write_bytes = fwrite($handle, $file_content);
				fclose($handle);
				if ($write_bytes) { return true; }
			}
			else { return false; }
		}
		//-------------------------------------------------
		// Full cache directory does NOT exist
		//-------------------------------------------------
		else {
			$base_cache_path = dirname($full_cache_path);

			if (is_writable($base_cache_path)) {
				$status = @mkdir($full_cache_path);
				if ($status) {
					if (is_writable($full_cache_path)) {
						$handle = fopen($full_cache_file, 'w');
						$write_bytes = fwrite($handle, $file_content);
						fclose($handle);
						if ($write_bytes) { return true; }
					}
					else { return false; }
				}
				else { return false; }
			}
			else { return false; }
		}

		return false;
	}
}

