<?php
//**************************************************************************
/**
* Data Access Object :: LWCMS Blog
*
* @package		phpOpenPlugins
* @subpackage	LWCMS
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 9/22/2009, Last updated: 1/12/2014
**/
//**************************************************************************

//**************************************************************************
// Include LWCMS Content Version Class
//**************************************************************************
include_once('lwcms_cv.class.php');

//**************************************************************************
// dao_lwcms_blog Class
//**************************************************************************
class dao_lwcms_blog
{
	//******************************************************************
	// Class Member Variables
	//******************************************************************
	protected $data_source;
	protected $site_id;
	protected $blog_id;
	protected $pub_status;
	protected $ver_field;
	protected $cache_dir;
	protected $entry_pull_cols;

	//******************************************************************
	// Constructor Function
	//******************************************************************
	public function __construct($ds, $site_id, $blog_id, $pub_status=1, $cache_dir=false)
	{
		if ($pub_status === false) { $pub_status = -1; }

		if (!$ds || !$site_id || !$blog_id) {
			$tmp_msg = "Invalid parameters passed! You must pass a data source handle, a site ID, and a blog ID.";
			$this->display_error(__FUNCTION__, $tmp_msg);
			return false;
		}
		else {
			$this->data_source = $ds;
			$this->site_id = $site_id;
			$this->blog_id = $blog_id;
			$this->pub_status = (int)$pub_status;
			$this->cache_dir = ((string)$cache_dir != '') ? (realpath($cache_dir)) : (false);
			if (!is_dir($this->cache_dir)) { $this->cache_dir = false; }
			$this->ver_field = ($this->pub_status) ? ('version_live') : ('version_dev');

			//---------------------------------------------------
			// Fields to Pull
			//---------------------------------------------------
			$this->entry_pull_cols = array(
				'id', 
				'site_id',
				'blog_id', 
				'cat_id',
				'create_user', 
				'create_date', 
				'post_date',
				'entry_title', 
				'entry_author',
				'entry_keywords', 
				'version_dev',
				'version_test', 
				'version_live',
				'metadata'
			);

		}
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

	//******************************************************************
	// Get Blog Information Function
	//******************************************************************
	public function get_info()
	{
		$strsql = "select * from site_blogs where site_id = ? and id = ? and active = 1";
		$params = array('ii', $this->site_id, $this->blog_id);
		return qdb_first_row($this->data_source, $strsql, $params);
	}

	//******************************************************************
	// Get Blog Entry Function
	//******************************************************************
	public function get_entry($entry_id=0)
	{
		$entry_cols = $this->build_entry_pull_cols('a.');
		settype($entry_id, 'int');
		$strsql = "
			select 
				{$entry_cols}, 
				b.author_name, 
				c.category
			from 
				site_blog_entries a, 
				site_blog_authors b, 
				site_blog_cats c
			where 
				a.site_id = b.site_id 
				and a.site_id = ? 
				and a.blog_id = ?
				and b.id = a.entry_author 
				and c.id = a.cat_id
				and a.id = ?
				and a.active = 1
				and a.{$this->ver_field} > 0
				limit 1
		";

		$params = array('iii', $this->site_id, $this->blog_id, $entry_id);
		$entries = qdb_exec($this->data_source, $strsql, $params);
		if (!$entries) { return false; }
		$this->add_content_to_entries($entries);
		if ($entries) { return $entries; }
		return false;
	}

	//******************************************************************
	// Get Blog Entries Function
	//******************************************************************
	public function get_entries($max_entries=5, $sort="desc")
	{
		//-------------------------------------------------
		// Set Variables
		//-------------------------------------------------
		$entry_cols = $this->build_entry_pull_cols('a.');
		settype($max_entries, 'int');
		if (strtolower($sort) != "desc") { $sort = "asc"; }

		//-------------------------------------------------
		// Build SQL Statement
		//-------------------------------------------------
		$strsql = "
			select 
				{$entry_cols}, 
				b.author_name, 
				c.category
			from 
				site_blog_entries a, 
				site_blog_authors b, 
				site_blog_cats c
			where 
				a.site_id = ? 
				and a.blog_id = ?
				and b.site_id = a.site_id 
				and b.id = a.entry_author 
				and c.id = a.cat_id
				and a.entry_author = b.id
				and a.active = 1
				and a.{$this->ver_field} > 0
			order by 
				post_date {$sort}, id desc
			limit ?
		";

		$params = array('iii', $this->site_id, $this->blog_id, $max_entries);
		$entries = qdb_exec($this->data_source, $strsql, $params);
		if (!$entries) { return array(); }
		$this->add_content_to_entries($entries);
		return $entries;
	}

	//******************************************************************
	// Get Blog Year/Month List Function
	//******************************************************************
	public function get_year_month_list()
	{
		$strsql = "
			select 
				year(post_date) as year, 
				month(post_date) as month, 
				monthname(post_date) as month_name, 
				count(*) as num_entries
			from 
				site_blog_entries
			where 
				site_id = ? 
				and blog_id = ?
				and active = 1
				and {$this->ver_field} > 0
			group by 
				month, year 
			order by 
				year desc, month desc
		";

		$params = array('ii', $this->site_id, $this->blog_id);
		return qdb_exec($this->data_source, $strsql, $params);
	}

	//******************************************************************
	// Get Blog Entries by Month and Year Function
	//******************************************************************
	public function get_entries_by_month_year($month, $year)
	{
		//-------------------------------------------------
		// Start and End Date
		//-------------------------------------------------
		$entry_cols = $this->build_entry_pull_cols('a.');
		$now = strtotime("{$month}/1/{$year}");
		$start_date = date('Y-m-d', $now);
		$end_date = date('Y-m-d',  strtotime("+1 Month", $now));
		
		$strsql = "
			select 
				{$entry_cols}, 
				b.author_name, 
				c.category
			from 
				site_blog_entries a, 
				site_blog_authors b, 
				site_blog_cats c
			where 
				a.site_id = ? 
				and a.blog_id = ?
				and a.post_date >= ? 
				and a.post_date <= ?
				and b.site_id = a.site_id 
				and c.id = a.cat_id
				and a.entry_author = b.id 
				and a.cat_id = c.id
				and a.active = 1
				and a.{$this->ver_field} > 0
			order by 
				post_date desc, id desc
		";

		$params = array('iiss', $this->site_id, $this->blog_id, $start_date, $end_date);
		$entries = qdb_exec($this->data_source, $strsql, $params);
		if (!$entries) { return false; }
		$this->add_content_to_entries($entries);
		return $entries;
	}

	//******************************************************************
	// Get Blog Entries by Category for the last year Function
	//******************************************************************
	public function get_entries_by_cat_last_365($cat_id)
	{
		return $this->get_entries_by_cat_last_x_days($cat_id, 365);
	}

	//******************************************************************
	// Get Blog Entries by Category for the last X Days Function
	//******************************************************************
	public function get_entries_by_cat_last_x_days($cat_id, $days)
	{
		$entry_cols = $this->build_entry_pull_cols('a.');
		settype($days, 'int');
		$days_ago = date('Y-m-d', strtotime("-{$days} Days"));

		$strsql = "
			select 
				{$entry_cols}, 
				b.author_name, 
				c.category
			from 
				site_blog_entries a, 
				site_blog_authors b, 
				site_blog_cats c
			where 
				a.site_id = ? 
				and a.blog_id = ?
				and a.cat_id = ?
				and a.post_date >= ?
				and b.site_id = a.site_id 
				and c.id = a.cat_id
				and a.entry_author = b.id 
				and a.cat_id = c.id 
				and a.active = 1
				and a.{$this->ver_field} > 0
			order by 
				post_date desc, id desc
		";

		$params = array('iiss', $this->site_id, $this->blog_id, $cat_id, $days_ago);
		$entries = qdb_exec($this->data_source, $strsql, $params);
		if (!$entries) { return false; }
		$this->add_content_to_entries($entries);
		return $entries;
	}

	//******************************************************************
	// Get Blog Category Function
	//******************************************************************
	public function get_category($cat_id)
	{
		$strsql = "
			select * from site_blog_cats
			where 
				site_id = ? 
				and blog_id = ? 
				and id = ?
		";

		$params = array('iii', $this->site_id, $this->blog_id, $cat_id);
		return qdb_lookup($this->data_source, $strsql, "category", $params);
	}

	//******************************************************************
	// Get All Blog Categories Function
	//******************************************************************
	public function get_all_categories()
	{
		$strsql = "
			select * from site_blog_cats
			where 
				site_id = ? 
				and blog_id = ?
			order by category
		";

		$params = array('ii', $this->site_id, $this->blog_id);
		return qdb_exec($this->data_source, $strsql, $params);
	}

	//******************************************************************
	// Get Blog Categories in the Last Year Function
	//******************************************************************
	public function get_cats_in_last_365()
	{
		return $this->get_cats_in_last_x_days(365);
	}

	//******************************************************************
	// Get Blog Categories in the Last X Number of Days Function
	//******************************************************************
	public function get_cats_in_last_x_days($days)
	{
		settype($days, 'int');
		$days_ago = date('Y-m-d', strtotime("-{$days} Days"));

		$strsql = "
			select 
				a.cat_id, 
				b.category, 
				count(*) as num_entries
			from 
				site_blog_entries a, 
				site_blog_cats b
			where 
				a.site_id = ? 
				and a.blog_id = ?
				and a.cat_id = b.id 
				and a.post_date >= ?
				and a.active = 1
				and a.{$this->ver_field} > 0
			group by 
				a.cat_id 
			order by 
				b.category
		";

		$params = array('iis', $this->site_id, $this->blog_id, $days_ago);
		return qdb_exec($this->data_source, $strsql, $params);
	}

	//******************************************************************
	// Get Blog Authors Function
	//******************************************************************
	public function get_authors()
	{
		$strsql = "
			select,
				b.id, 
				b.author_name 
			from 
				site_blog_entries a, 
				site_blog_authors b
			where 
				a.site_id = ? 
				and a.blog_id = ? 
				and b.site_id = a.site_id
				and a.active = 1
				and a.{$this->ver_field} > 0
			order by 
				b.author_name
		";

		$params = array('ii', $this->site_id, $this->blog_id);
		return qdb_exec($this->data_source, $strsql, $params, 'id:author_name');
	}

	//*************************************************************************
	// Build Entry Pull Columns Function
	//*************************************************************************
    protected function build_entry_pull_cols($prefix=false)
    {
    	$tmp_cols = array();
    	foreach ($this->entry_pull_cols as $c) {
	    	$tmp_cols[] = $prefix . $c;
    	}
    	return implode(', ', $tmp_cols);
    }

	//******************************************************************
	// Add Content to Entries
	//******************************************************************
	protected function add_content_to_entries(&$entries)
	{
		if (!$entries || !is_array($entries)) { return false; }
		foreach ($entries as $key => &$entry) {
			$tmp_content = $this->get_entry_content($entry);
			if ($tmp_content !== false) {
				$entry['entry_content'] = $tmp_content;
			}
			else {
				unset($entries[$key]);
			}

			$post_date_stamp = strtotime($entry['post_date']);
			if ($post_date_stamp > 0) {
				$entry['disp_post_date'] = date('n/j/Y', $post_date_stamp);
				$entry['disp_post_date2'] = date('F j Y', $post_date_stamp);
				$entry['disp_post_datetime'] = date('n/j/Y, g:i a', $post_date_stamp);
				$entry['disp_post_datetime2'] = date('F j Y, g:i a', $post_date_stamp);
			}
			else {
				$entry['disp_post_date'] = false;
				$entry['disp_post_date2'] = false;
				$entry['disp_post_datetime'] = false;
				$entry['disp_post_datetime2'] = false;
			}
		}
	}

	//******************************************************************
	// Get Entry Content Function
	//******************************************************************
	protected function get_entry_content($entry)
	{
		if (!$entry) { return false; }

		//-------------------------------------------------
		// Set Current Version
		//-------------------------------------------------
		$curr_ver = $entry[$this->ver_field];

		//-------------------------------------------------
		// If cache directory is set, try to use content cache
		//-------------------------------------------------
		if ($this->cache_dir) {

			//-------------------------------------------------
			// Build paths and files
			//-------------------------------------------------
			$cache_folder = substr($entry['create_date'], 0, 7);
			$full_cache_path = $this->cache_dir . '/' . $cache_folder;
			$cache_file = 'blog_' . $entry['id'] . '_' . $curr_ver . '.html';
			$full_cache_file = $full_cache_path . '/' . $cache_file;

			//-------------------------------------------------
			// Attempt to pull content from cache
			//-------------------------------------------------
			if ($file_content = LWCMS_CV::get_cached_version_content($full_cache_file)) {
				return $file_content;
			}
			//-------------------------------------------------
			// If content cache is not accessible or does not exist
			//-------------------------------------------------
			else {
				//-------------------------------------------------
				// Pull content from version content
				//-------------------------------------------------
				$file_content = LWCMS_CV::get_version_content($this->data_source, $curr_ver);

				//-------------------------------------------------
				// Content Exists, try to cache it
				//-------------------------------------------------
				if ($file_content) {
					$status = LWCMS_CV::set_cached_version_content($file_content, $full_cache_path, $full_cache_file);
				}
				return $file_content;
			}
		}
		else {
			return LWCMS_CV::get_version_content($this->data_source, $curr_ver);
		}

		return false;
	}

}

