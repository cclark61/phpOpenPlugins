<?php
//**************************************************************************
/**
* LWCMS Blog Plugin
*
* @package		phpOpenPlugins
* @subpackage	LWCMS
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 9/28/2009, Last updated: 3/1/2013
**/
//**************************************************************************

//**************************************************************
// Required Classes / Libraries
//**************************************************************
include('POP_lwcms/dao_lwcms_blog.class.php');

//**************************************************************
//**************************************************************
// LWCMS Blog Class
//**************************************************************
//**************************************************************
class LWCMS_Blog
{

	//*************************************************************************
	// Class Variables
	//*************************************************************************
	public $dao;

	//*************************************************************************
	// Constructor Function
	//*************************************************************************
    public function __construct($ds, $site_id, $blog_id, $pub_status=1, $cache_dir=false)
    {
    	$this->dao = new dao_lwcms_blog($ds, $site_id, $blog_id, $pub_status, $cache_dir);
    }

	//*************************************************************************
	// Destructor Function
	//*************************************************************************
	public function __destruct() {}

	//*************************************************************************
	// Object Conversion to String Function
	//*************************************************************************
    public function __toString() {}

	//*************************************************************************
	// Display Error Function
	//*************************************************************************
    protected function display_error($function, $error_msg)
    {
    	$tmp_msg = 'Class [' . __CLASS__ . "]::{$function}() - ";
    	$tmp_msg .= "Error: {$error_msg}";
    	trigger_error($tmp_msg);
    }

	//**************************************************************
	// Check for Required Parameters
	//**************************************************************
	public function parameters_validation($req_args, $args)
	{
		$errors = 0;
		foreach ($req_args as $arg) {
			if (!isset($args[$arg])) { $errors++; }
		}
		return $errors;
	}

	//**************************************************************
	// Blog RSS Feed Data Function
	//**************************************************************
	public function rss_feed_data($args)
	{
		//-------------------------------------------------
		// Check for required parameters
		//-------------------------------------------------
		$req_args = array('url_stub', 'feed_link', 'title', 'description');
		if ($this->parameters_validation($req_args, $args) > 0) {
			$this->display_error(__FUNCTION__, 'Not all required parameters passed.');
			return false;
		}
	
		//-------------------------------------------------
		// Extract Arguments
		//-------------------------------------------------
		extract($args);
		$param_errors = 0;

		//-------------------------------------------------
		// Check / Set Parameters
		//-------------------------------------------------
		if (!isset($max_entries)) { $max_entries = 25; }

		//-------------------------------------------------
		// URL Format
		//-------------------------------------------------
		if (!isset($url_format)) { $url_format = 1; }

		//-------------------------------------------------
		// Title
		//-------------------------------------------------
		if (!isset($title)) {
			$this->display_error(__FUNCTION__, 'No RSS feed title set!');
			$param_errors++;
		}

		//-------------------------------------------------
		// Description
		//-------------------------------------------------
		if (!isset($description)) {
			$this->display_error(__FUNCTION__, 'No RSS feed description set!');
			$param_errors++;
		}

		//-------------------------------------------------
		// Parameter Errors?
		//-------------------------------------------------
		if ($param_errors) { return false; }
	
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
		$blog_entries = $this->dao->get_entries($max_entries);
		//print_array($blog_entries);
		
		$ce = array();
		foreach ($blog_entries as $key => $be) {
		
			//-------------------------------------------------
			// URL Format
			//-------------------------------------------------
			switch ($url_format) {
	
				//-------------------------------------------------
				// [Year]/[Month]/[Day]/[Title]
				case 2:
					break;
	
				//-------------------------------------------------
				// [Blog Entry ID]/[Title]
				//-------------------------------------------------
				default:
					$tmp_link = $url_stub . "/{$be}[id]/" . seo_friendly_str($be['entry_title']) . '.html';
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
			$tmp_desc = strip_tags($be['entry_content']);
			if (strlen($tmp_desc) > 250) { $ce[$key]['description'] = substr($tmp_desc, 0, 250) . '[...]'; }
			else { $ce[$key]['description'] = $tmp_desc; }
	
			//-------------------------------------------------
			// Encoded Content
			//-------------------------------------------------
			$ce[$key]['content'] = $be['entry_content'];
	
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
	// Blog Nav Functions
	//**************************************************************
	//**************************************************************
	
	//*********************************************************
	// Recent Posts Nav Function
	//*********************************************************
	public function recent_posts_nav($args=array())
	{
		//-------------------------------------------------
		// Check for required parameters
		//-------------------------------------------------
		$req_args = array('url_stub');
		if ($this->parameters_validation($req_args, $args) > 0) {
			$this->display_error(__FUNCTION__, 'Not all required parameters passed.');
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
		$blog_ents = $this->dao->get_entries(10);

		$recents_nav = array();
		$recents_nav['title'] = $title;
		$recents_nav['nav_items'] = array();
		$recents_nav['url_stub'] = $url_stub;
		foreach ($blog_ents as $post) {
			extract($post);
			$tmp = $post;
			unset($tmp['entry_content']);
			$tmp['desc'] = $entry_title;
			$seo_title = seo_friendly_str($entry_title);
			$tmp['link'] = "{$url_stub}/{$id}/{$seo_title}" . '.html';
			$tmp['key'] = $id;
			
			$recents_nav['nav_items'][] = $tmp;
		}
	
		return $recents_nav;
	}
	
	//*********************************************************
	// Categories Nav Function
	//*********************************************************
	public function category_nav($args=array())
	{
		//-------------------------------------------------
		// Check for required parameters
		//-------------------------------------------------
		$req_args = array('url_stub');
		if ($this->parameters_validation($req_args, $args) > 0) {
			$this->display_error(__FUNCTION__, 'Not all required parameters passed.');
			return false;
		}
	
		//-------------------------------------------------
		// Extract Paramters
		//-------------------------------------------------
		extract($args);
		if (!isset($title)) { $title = 'Categories'; }
		if (!isset($zero_category)) { $zero_category = 'General'; }
	
		//-------------------------------------------------
		// Pull Data
		//-------------------------------------------------
		$blog_cats = $this->dao->get_cats_in_last_365();
		
		$cats_nav = array();
		$cats_nav['title'] = $title;
		$cats_nav['nav_items'] = array();
		$cats_nav['url_stub'] = $url_stub;
		foreach ($blog_cats as $cat) {
			extract($cat);
			$tmp = $cat;
			unset($tmp['entry_content']);
			if ($cat_id == 0) { $category = $zero_category; }
			$tmp['desc'] = "{$category} ({$num_entries})";
			$seo_cat_name = seo_friendly_str($category);
			$tmp['link'] = "{$url_stub}/{$cat_id}/{$seo_cat_name}" . '.html';
			$tmp['key'] = $cat_id;

			$cats_nav['nav_items'][] = $tmp;
		}
	
		return $cats_nav;
	}

	//*********************************************************
	// Archive Nav Function
	//*********************************************************
	public function archive_nav($args=array())
	{
		//-------------------------------------------------
		// Check for required parameters
		//-------------------------------------------------
		$req_args = array('url_stub');
		if ($this->parameters_validation($req_args, $args) > 0) {
			$this->display_error(__FUNCTION__, 'Not all required parameters passed.');
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
		$blog_archs = $this->dao->get_year_month_list();

		$archs_nav = array();
		$archs_nav['title'] = $title;
		$archs_nav['nav_items'] = array();
		$archs_nav['url_stub'] = $url_stub;
		foreach ($blog_archs as $arch) {
			extract($arch);
			$tmp = $arch;
			unset($tmp['entry_content']);
			$tmp['desc'] = "{$month_name} {$year} ({$num_entries})";
			$tmp['link'] = "{$url_stub}/{$year}/{$month}/";
			$tmp['key'] = "{$year}/{$month}";

			$archs_nav['nav_items'][] = $tmp;
		}
	
		return $archs_nav;
	}
}

