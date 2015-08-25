<?php
//**************************************************************************
/**
* LWCMS Dynamic Content Plugin
*
* @package		phpOpenPlugins
* @subpackage	LWCMS
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 12/20/2009, Last updated: 2/25/2013
**/
//**************************************************************************

//**************************************************************
// Required Classes / Libraries
//**************************************************************
include('POP_lwcms/dao_lwcms_dce.class.php');
include('POP_lwcms/dao_lwcms_dcf.class.php');

//**************************************************************
//**************************************************************
// LWCMS Dynamic Content Class
//**************************************************************
//**************************************************************
class LWCMS_DC
{

	//*************************************************************************
	// Display Error Function
	//*************************************************************************
    protected static function display_error($function, $error_msg)
    {
    	$tmp_msg = 'Class [' . __CLASS__ . "]::{$function}() - ";
    	$tmp_msg .= "Error: {$error_msg}";
    	trigger_error($tmp_msg);
    }

	//**************************************************************
	// Check for Required Parameters
	//**************************************************************
	protected static function parameters_validation($req_args, $args)
	{
		$errors = 0;
		foreach ($req_args as $arg) {
			if (!isset($args[$arg])) { $errors++; }
		}
		return $errors;
	}
	
	//**************************************************************
	// Get Content by Keytag Function
	// Required Arguments (array):
	// -> ds: data source handle
	// -> site_id: Site ID
	// -> pub_status: Publish Status (0 - Draft, 1 - Published)
	// -> cache_dir: Cache directory ('false' if none) 
	// -> keytag
	//**************************************************************
	public static function get_content_by_keytag($args=array())
	{
		//-------------------------------------------------
		// Check for required arguments
		//-------------------------------------------------
		$req_args = array('ds', 'site_id', 'pub_status', 'cache_dir', 'keytag');
		if (self::parameters_validation($req_args, $args) > 0) {
			self::display_error(__FUNCTION__, 'Not all required parameters passed.');
			return false;
		}
	
		//-------------------------------------------------
		// Extract Arguments
		//-------------------------------------------------
		extract($args);
		
		//-------------------------------------------------
		// Get Info for Content Entry
		//-------------------------------------------------
		$entry_dao = new dao_lwcms_dce($args);
		$load_status = $entry_dao->load_entry_by_keytag($keytag);
	
		//-------------------------------------------------
		// Check Load Status
		//-------------------------------------------------
		if ($load_status) {
	
			//-------------------------------------------------
			// Pull Entry Info
			//-------------------------------------------------
			$entry_info = $entry_dao->get_entry_info();
			if ($entry_info) {
				$entry_info['content'] = $entry_dao->get_entry_content();
				return $entry_info;
			}		
		}
		else { return false; }
	}
	
	//**************************************************************
	// Get Latest Folder Entry Content Function
	// Required Arguments (array):
	// -> ds: data source handle
	// -> site_id: Site ID
	// -> pub_status: Publish Status (0 - Draft, 1 - Published)
	// -> cache_dir: Cache directory ('false' if none) 
	// -> keytag
	//**************************************************************
	public static function glfec($args=array())
	{
		//-------------------------------------------------
		// Check for required parameters
		//-------------------------------------------------
		$req_args = array('ds', 'site_id', 'pub_status', 'cache_dir', 'keytag');
		if (self::parameters_validation($req_args, $args) > 0) {
			self::display_error(__FUNCTION__, 'Not all required parameters passed.');
			return false;
		}
	
		//-------------------------------------------------
		// Extract Arguments
		//-------------------------------------------------
		extract($args);
		
		//-------------------------------------------------
		// Get Info for Content Folder
		//-------------------------------------------------
		$folder_dao = new dao_lwcms_dcf($args);
		$load_status = $folder_dao->load_folder_by_keytag($keytag);
	
		//-------------------------------------------------
		// Check Load Status
		//-------------------------------------------------
		if ($load_status) {
	
			//-------------------------------------------------
			// Pull Folder Info
			//-------------------------------------------------
			$folder_info = $folder_dao->get_folder_info();
			$folder_id = $folder_info['id'];
	
			if ($folder_info) {
				$entry_id = $folder_dao->get_latest_folder_entry();
				$entry_dao = new dao_lwcms_dce($args);
				$load_status2 = $entry_dao->load_entry_by_id($entry_id);
				
				if ($load_status2) {
					$entry_info = $entry_dao->get_entry_info();
					if ($entry_info) {
						$entry_info['content'] = $entry_dao->get_entry_content();
						return $entry_info;
					}
				}
			}
		}
	
		return false;
	}

	//**************************************************************
	// Folder RSS Feed Data Function
	//**************************************************************
	public static function folder_rss_feed_data($folder_dao, $args)
	{
		//-------------------------------------------------
		// Check for required parameters
		//-------------------------------------------------
		$req_args = array('url_stub', 'feed_link', 'title', 'description');
		if (self::parameters_validation($req_args, $args) > 0) {
			self::display_error(__FUNCTION__, 'Not all required parameters passed.');
			return false;
		}
	
		//-------------------------------------------------
		// Extract Arguments
		//-------------------------------------------------
		extract($args);
	
		//-------------------------------------------------
		// Set Default Parameters if not set
		//-------------------------------------------------
		if (!isset($max_entries)) { $max_entries = 25; }
		if (!isset($url_format)) { $url_format = 1; }
	
		//-------------------------------------------------
		// RSS Feed Channel Header Data
		//-------------------------------------------------
		$feed_data = array();
		$feed_data['channel_elements']['title'] = $title;
		$feed_data['channel_elements']['link'] = $feed_link;
		$feed_data['channel_elements']['description'] = $description;
		$feed_data['channel_elements']['language'] = 'en-us';
		$feed_data['channel_elements']['lastBuildDate'] = date('D, d M Y H:i:s O', time());
		if (isset($atom_link)) {
			$feed_data['channel_elements']['atom_link'] = $atom_link;
		}
	
		//-------------------------------------------------
		// RSS Feed Entries
		//-------------------------------------------------
		$entries = $folder_dao->get_folder_entries($max_entries);
		//print_array($entries);
	
		$ce = array();
		foreach ($entries as $key => $be) {
	
			//-------------------------------------------------
			// Set entry content
			//-------------------------------------------------
			$entry_dao = new dao_lwcms_dce($dyn_cont_args);
			$load_status2 = $entry_dao->load_entry_by_id($be['id']);
			$be['content'] = ($load_status2) ? ($entry_dao->get_entry_content()) : ('');
	
			//-------------------------------------------------
			// URL Format
			//-------------------------------------------------
			switch ($url_format) {
	
				// [Year]/[Month]/[Day]/[Title]
				case 2:
					break;
	
				// [Entry ID]/[Title]
				default:
					$tmp_link = $url_stub . "/$be[id]/" . seo_friendly_str($be['entry_title']) . '.html';
					break;
			}
	
			//-------------------------------------------------
			// Title
			//-------------------------------------------------
			$ce[$key]['title'] = $be['entry_title'];
	
			//-------------------------------------------------
			// Link
			//-------------------------------------------------
			$ce[$key]['link'] = $tmp_link;
	
			//-------------------------------------------------
			// Description
			//-------------------------------------------------
			$tmp_desc = strip_tags($be['content']);
			if (strlen($tmp_desc) > 250) { $ce[$key]['description'] = substr($tmp_desc, 0, 250) . '[...]'; }
			else { $ce[$key]['description'] = $tmp_desc; }
	
			//-------------------------------------------------
			// Encoded Content
			//-------------------------------------------------
			$ce[$key]['content'] = $be['content'];
	
			//-------------------------------------------------
			// Publish Date
			//-------------------------------------------------
			$ce[$key]['pubDate'] = date('D, d M Y H:i:s O', strtotime($be['post_date']));
	
			//-------------------------------------------------
			// Category
			//-------------------------------------------------
			if ($be['category'] != '') { $ce[$key]['category'] = $be['category']; }
	
			//-------------------------------------------------
			// GUID
			//-------------------------------------------------
			$ce[$key]['guid'] = $tmp_link;
		}
		
		$feed_data['channel_entries'] = $ce;
		return xml_escape_array($feed_data);
	}
	
	//**************************************************************
	//**************************************************************
	// Nav Functions
	//**************************************************************
	//**************************************************************
	
	//*********************************************************
	// Folder Recent Posts Nav Function
	//*********************************************************
	public static function folder_recent_posts_nav($folder_dao, $args=array())
	{
		//-------------------------------------------------
		// Check for required parameters
		//-------------------------------------------------
		$req_args = array('url_stub');
		if (self::parameters_validation($req_args, $args) > 0) {
			self::display_error(__FUNCTION__, 'Not all required parameters passed.');
			return false;
		}
	
		//-------------------------------------------------
		// Extract Paramters
		//-------------------------------------------------
		extract($args);
	
		//-------------------------------------------------
		// Set Default Parameters if not set
		//-------------------------------------------------
		if (!isset($max_entries)) { $max_entries = 10; }
		if (!isset($title)) { $title = 'Recent Posts'; }
	
		//-------------------------------------------------
		// Pull data
		//-------------------------------------------------
		$entries = $folder_dao->get_folder_entries($max_entries);
		
		$recents_nav = array();
		$recents_nav['title'] = $title;
		$recents_nav['nav_items'] = array();
		$recents_nav['url_stub'] = $url_stub;
		foreach ($entries as $ent) {
			extract($ent);
			$tmp = array();
			$tmp['desc'] = $entry_title;
			$seo_title = seo_friendly_str($entry_title);
			$tmp['link'] = "{$url_stub}/{$id}/{$seo_title}" . '.html';
			$tmp['key'] = $id;
			$recents_nav['nav_items'][] = $tmp;
		}
	
		return $recents_nav;
	}
	
	//*********************************************************
	// Folder Categories Nav Function
	//*********************************************************
	public static function folder_category_nav($folder_dao, $args=array())
	{
		//-------------------------------------------------
		// Check for required parameters
		//-------------------------------------------------
		$req_args = array('url_stub');
		if (self::parameters_validation($req_args, $args) > 0) {
			self::display_error(__FUNCTION__, 'Not all required parameters passed.');
			return false;
		}
	
		//-------------------------------------------------
		// Extract Paramters
		//-------------------------------------------------
		extract($args);
		if (!isset($title)) { $title = 'Categories'; }
		if (!isset($zero_category)) { $zero_category = 'General'; }
	
		//-------------------------------------------------
		// Pull data
		//-------------------------------------------------
		$cats = $folder_dao->get_used_folder_categories();
		
		$cats_nav = array();
		$cats_nav['title'] = $title;
		$cats_nav['nav_items'] = array();
		$cats_nav['url_stub'] = $url_stub;
		foreach ($cats as $cat) {
			extract($cat);
			if ($cat_id == 0) { $category = $zero_category; }
			$tmp = array();
			$tmp['desc'] = "{$category} ({$count})";
			$seo_cat_name = seo_friendly_str($category);
			$tmp['link'] = "{$url_stub}/{$cat_id}/{$seo_cat_name}" . '.html';
			$tmp['key'] = $cat_id;
			$cats_nav['nav_items'][] = $tmp;
		}
	
		return $cats_nav;
	}
	
	//*********************************************************
	// Folder Archive Nav Function
	//*********************************************************
	public static function folder_archive_nav($folder_dao, $args=array())
	{
		//-------------------------------------------------
		// Check for required parameters
		//-------------------------------------------------
		$req_args = array('url_stub');
		if (self::parameters_validation($req_args, $args) > 0) {
			self::display_error(__FUNCTION__, 'Not all required parameters passed.');
			return false;
		}
	
		//-------------------------------------------------
		// Extract Paramters
		//-------------------------------------------------
		extract($args);
	
		//-------------------------------------------------
		// Set Default Parameters if not set
		//-------------------------------------------------
		if (!isset($title)) { $title = 'Archive'; }
	
		//-------------------------------------------------
		// Pull data
		//-------------------------------------------------
		$archs = $folder_dao->get_folder_year_month_list();
	
		$archs_nav = array();
		$archs_nav['title'] = $title;
		$archs_nav['nav_items'] = array();
		$archs_nav['url_stub'] = $url_stub;
		foreach ($archs as $arch) {
			extract($arch);
			$tmp = array();
			$tmp['desc'] = "{$month_name} {$year} ({$num_entries})";
			$tmp['link'] = "{$url_stub}/{$year}/{$month}/";
			$tmp['key'] = "{$year}/{$month}";
			$archs_nav['nav_items'][] = $tmp;
		}
	
		return $archs_nav;
	}
}

