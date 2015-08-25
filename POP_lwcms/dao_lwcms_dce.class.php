<?php
//**************************************************************************
/**
* Data Access Object :: LWCMS Dynamic Content Entry
*
* @package		phpOpenPlugins
* @subpackage	LWCMS
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 12/20/2009, Last updated: 5/9/2013
**/
//**************************************************************************

//**************************************************************************
// Include LWCMS Content Version Class
//**************************************************************************
include_once('lwcms_cv.class.php');

//**************************************************************************
// dao_lwcms_dce Class
//**************************************************************************
class dao_lwcms_dce
{
	//******************************************************************
	// Class Member Variables
	//******************************************************************
	protected $data_source;
	protected $site_id;
	protected $dyn_cont_id;
	protected $entry_type;
	protected $entry_info;
	protected $cache_dir;
	protected $pub_status;
	protected $ver_field;
	protected $id;
	protected $parent;

	//******************************************************************
	// Constructor Function
	//******************************************************************
	public function __construct($ds, $site_id=0, $pub_status=1, $cache_dir=false)
	{
		//-------------------------------------------------
		// Check if parameters passed as an array
		//-------------------------------------------------
		if (is_array($ds)) { extract($ds); }

		//-------------------------------------------------
		// Check for valid parameters
		//-------------------------------------------------
		if (!$ds || !$site_id) {
			$tmp_msg = "Invalid parameters passed! You must pass a data source handle and a site ID";
			$this->display_error(__FUNCTION__, $tmp_msg);
			return false;
		}
		else {
			$this->data_source = $ds;
			$this->site_id = $site_id;
			$this->pub_status = (int)$pub_status;
			$this->ver_field = ($this->pub_status) ? ('version_live') : ('version_dev');
			$this->cache_dir = ((string)$cache_dir != '') ? (realpath($cache_dir)) : (false);
			if (!is_dir($this->cache_dir)) { $this->cache_dir = false; }
			$this->dyn_cont_id = false;
			$this->entry_type = false;
			$this->entry_info = false;
			$this->id = false;
			$this->parent = false;
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
	// Load Record Information By URL Tag / Key Function
	//******************************************************************
	public function load_entry_by_keytag($url_tag)
	{
		//-------------------------------------------------
		// Check that URL Tag is valid
		//-------------------------------------------------
		if (!$url_tag) { return false; }

		$strsql = "
				select
					id, 
					site_id,
					parent, 
					node_path, 
					create_date, 
					mod_date, 
					post_date,
					entry_title, 
					entry_type, 
					url_tag,
					index_content, 
					version_dev, 
					version_test, 
					version_live,
					metadata,
					entry_author,
					cat_id
				from 
					site_entries
				where 
					site_id = ? 
					and url_tag = ? 
					and entry_type = 2
					and active = 1
		";

		$entries = qdb_exec($this->data_source, $strsql, array('is', $this->site_id, $url_tag));
		if (isset($entries[0])) {
			$this->initialize($entries[0]);
			return true;
		}
		else { return false; }
	}

	//******************************************************************
	// Load Record Information By ID Function
	//******************************************************************
	public function load_entry_by_id($entry_id)
	{
		//-------------------------------------------------
		// Check that entry ID is valid
		//-------------------------------------------------
		if (!$entry_id) { return false; }
		
		$strsql = "
				select
					id, 
					site_id,
					parent, 
					node_path, 
					create_date, 
					mod_date, 
					post_date,
					entry_title, 
					entry_type, 
					url_tag,
					index_content, 
					version_dev, 
					version_test, 
					version_live,
					metadata,
					entry_author,
					cat_id
				from 
					site_entries
				where 
					site_id = ? 
					and id = ? 
					and entry_type = 2
					and active = 1
		";

		$entries = qdb_exec($this->data_source, $strsql, array('ii', $this->site_id, $entry_id));
		if (isset($entries[0])) {
			$this->initialize($entries[0]);
			return true;
		}
		else { return false; }
	}

	//******************************************************************
	// Initialize Function
	//******************************************************************
	protected function initialize(&$entry)
	{
		$this->id = $entry['id'];
		$this->parent = $entry['parent'];
		$this->entry_type = $entry['entry_type'];
		
		//-------------------------------------------------
		// Set Author
		//-------------------------------------------------
		$tmp = $this->get_author_info($entry['entry_author']);
		$entry['author_name'] = (isset($tmp['author_name'])) ? ($tmp['author_name']) : ('');

		//-------------------------------------------------
		// Set Category
		//-------------------------------------------------
		$tmp = $this->get_category_info($entry['cat_id']);
		$entry['category'] = (isset($tmp['category'])) ? ($tmp['category']) : ('');

		//-------------------------------------------------
		// Set Entry Info
		//-------------------------------------------------
		$this->entry_info = $entry;

		return true;
	}

	//******************************************************************
	// Get Entry Content Function
	//******************************************************************
	public function get_entry_content()
	{
		if ($this->entry_info && $this->entry_type == 2) {

			//-------------------------------------------------
			// Set Current Version
			//-------------------------------------------------
			$curr_ver = $this->entry_info[$this->ver_field];

			//-------------------------------------------------
			// If cache directory is set, try to use content cache
			//-------------------------------------------------
			if ($this->cache_dir) {

				//-------------------------------------------------
				// Build paths and files
				//-------------------------------------------------
				$cache_folder = substr($this->entry_info['create_date'], 0, 7);
				$full_cache_path = $this->cache_dir . '/' . $cache_folder;
				$cache_file = 'dyn_cont_' . $this->entry_info['id'] . '_' . $curr_ver . '.html';
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
		}
		else { return false; }
	}

	//******************************************************************
	// Get Entry Meta Data Function
	//******************************************************************
	public function get_entry_meta_data()
	{
		if (isset($this->entry_info['metadata']) && $this->entry_info['metadata']) {
			$tmp = explode("\n", $this->entry_info['metadata']);
			$tmp2 = array();
			foreach ($tmp as $metaline) {
				if (!$metaline) { continue; }
				else {
					$tmp3 = explode(':::', $metaline);
					if (count($tmp3) <= 1) { continue; }
					else {
						$key = $tmp3[0];
						unset($tmp3[0]);
						if (count($tmp3) > 1) {
							$tmp2[$key] = array();
							foreach ($tmp3 as $key2 => $val2) { $tmp2[$key]['val' . $key2] = $val2; }
						}
						else { $tmp2[$key] = $tmp3[1]; }
					}
				}
			}
			return $tmp2;
		}
		else { return array(); }
	}
	
	//******************************************************************
	// Get Entry Info Function
	//******************************************************************
	public function get_entry_info() { return $this->entry_info; }

	//******************************************************************
	// Get Entry Type Function
	//******************************************************************
	public function get_entry_type() { return $this->entry_type; }

	//******************************************************************
	// Get Version Field Function
	//******************************************************************
	public function get_version_field() { return $this->ver_field; }

	//*****************************************************************************
	//*****************************************************************************
	// Protected Class Methods
	//*****************************************************************************
	//*****************************************************************************

	//******************************************************************
	// Get Author Info Function
	//******************************************************************
	protected function get_author_info($author_id=0)
	{
		$author_id += 0;
		if (!$author_id) { return false; }

		$strsql = "
			select
				id,
				blog_id,
				author_name 
			from 
				site_blog_authors
			where 
				site_id = ? 
				and id = ? 
		";

		$authors = qdb_exec($this->data_source, $strsql, array('ii', $this->site_id, $author_id));
		return (isset($authors[0])) ? ($authors[0]) : (false);
	}

	//******************************************************************
	// Get Category Function
	//******************************************************************
	protected function get_category_info($cat_id=0)
	{
		$cat_id += 0;
		if (!$cat_id) { return false; }

		$strsql = "
			select
				id,
				category,
				active 
			from 
				site_entry_cats
			where 
				site_id = ?
				and folder_id = ? 
				and id = ?
			";

		$cats = qdb_exec($this->data_source, $strsql, array('iii', $this->site_id, $this->parent, $cat_id));
		return (isset($cats[0])) ? ($cats[0]) : (false);
	}

}

