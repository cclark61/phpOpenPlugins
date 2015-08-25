<?php
//**************************************************************************
/**
* Data Access Object :: LWCMS Dynamic Content Folder
*
* @package		phpOpenPlugins
* @subpackage	LWCMS
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 12/20/2009, Last updated: 3/1/2013
**/
//**************************************************************************

class dao_lwcms_dcf
{
	//******************************************************************
	// Class Member Variables
	//******************************************************************
	protected $data_source;
	protected $site_id;
	protected $dyn_cont_id;
	protected $entry_type;
	protected $entry_info;
	protected $pub_status;
	protected $ver_field;
	protected $id;
	protected $parent;
	protected $entry_pull_cols;

	//******************************************************************
	// Constructor Function
	//******************************************************************
	public function __construct($ds, $site_id=0, $pub_status=1)
	{
		//-------------------------------------------------
		// Check if parameters passed as an array
		//-------------------------------------------------
		if (is_array($ds)) { extract($ds); }

		//-------------------------------------------------
		// check for valid parameters
		//-------------------------------------------------
		if (!$ds || !$site_id) {
			$tmp_msg = "Invalid parameters passed!";
			$tmp_msg .= " You must pass a data source handle and a site ID";
			$this->display_error(__FUNCTION__, $tmp_msg);
			return false;
		}
		else {
			$this->data_source = $ds;
			$this->site_id = $site_id;
			$this->dyn_cont_id = false;
			$this->entry_type = false;
			$this->entry_info = false;
			$this->id = false;
			$this->parent = false;
			$this->pub_status = (int)$pub_status;
			$this->ver_field = ($this->pub_status) ? ('version_live') : ('version_dev');

			//---------------------------------------------------
			// Folder Fields to Pull
			//---------------------------------------------------
			$this->entry_pull_cols = "
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
				metadata,
				entry_author,
				cat_id
			";
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
	public function load_folder_by_keytag($url_tag)
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
					entry_title, 
					entry_type, 
					url_tag,
					index_content, 
					version_dev, 
					version_test, 
					version_live,
					metadata
				from 
					site_entries
				where 
					site_id = ? 
					and url_tag = ? 
					and entry_type = 1
		";

		$entries = qdb_exec($this->data_source, $strsql, array('is', $this->site_id, $url_tag));
		if (isset($entries[0])) {
			$this->id = $entries[0]['id'];
			$this->parent = $entries[0]['parent'];
			$this->entry_type = $entries[0]['entry_type'];
			$this->entry_info = $entries[0];
			return true;
		}
		else { return false; }
	}

	//******************************************************************
	// Get Folder Info Function
	//******************************************************************
	public function get_folder_info() { return $this->entry_info; }

	//******************************************************************
	// Get Folder Meta Data Function
	//******************************************************************
	public function get_folder_meta_data()
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
	// Get Latest Folder Entry Function
	//******************************************************************
	public function get_latest_folder_entry()
	{
		$strsql = "
			select 
				id, 
				create_date 
			from 
				site_entries
			where 
				site_id = ? 
				and parent = ?
				and entry_type = 2
				and {$this->ver_field} > 0 
				and active = 1
			order by post_date desc limit 1
		";
		
		return qdb_lookup($this->data_source, $strsql, 'id', array('ii', $this->site_id, $this->id));
	}

	//******************************************************************
	// Get Number of Folder Entries Function
	//******************************************************************
	public function get_number_folder_entries()
	{
		$strsql = "
			select 
				count(*) as count
			from 
				site_entries 
			where 
				site_id = ? 
				and parent = ? 
				and entry_type = 2 
				and {$this->ver_field} > 0 
				and active = 1
		";

		// Pull Entries and return them
		return qdb_lookup($this->data_source, $strsql, 'count', array('ii', $this->site_id, $this->id));
	}

	//******************************************************************
	// Get Folder Entries Function
	//******************************************************************
	public function get_folder_entries($max_entries=25, $sort="desc")
	{
		//-------------------------------------------------
		// Set Variables
		//-------------------------------------------------
		settype($max_entries, 'int');
		if ($sort != "desc") { $sort = "asc"; }

		//-------------------------------------------------
		// Build SQL Statement
		//-------------------------------------------------
		$strsql = "
			select 
				{$this->entry_pull_cols}
			from 
				site_entries
			where 
				site_id = ? 
				and parent = ?
				and entry_type = 2
				and active = 1 
				and {$this->ver_field} > 0 
			order by post_date {$sort}, id desc
			limit ?
		";

		//-------------------------------------------------
		// Pull Entries, Polish and Return
		//-------------------------------------------------
		$entries = qdb_exec($this->data_source, $strsql, array('iii', $this->site_id, $this->id, $max_entries));
		$this->polish_entries($entries);
		return $entries;
	}

	//******************************************************************
	// Get Folder Year/Month List Function
	//******************************************************************
	public function get_folder_year_month_list()
	{
		//-------------------------------------------------
		// Build SQL Statement
		//-------------------------------------------------
		$strsql = "
			select 
				year(post_date) as year, 
				month(post_date) as month, 
				monthname(post_date) as month_name, 
				count(*) as num_entries
			from 
				site_entries
			where 
				site_id = ? 
				and parent = ?
				and entry_type = 2
				and active = 1 
				and {$this->ver_field} > 0 
			group by month, year 
			order by year desc, month desc
		";

		// Pull Data and Return
		return qdb_exec($this->data_source, $strsql, array('ii', $this->site_id, $this->id));
	}

	//******************************************************************
	// Get Folder Entries by Month and Year Function
	//******************************************************************
	public function get_folder_entries_by_month_year($month, $year)
	{
		//-------------------------------------------------
		// Start and End Date
		//-------------------------------------------------
		$now = strtotime("{$month}/1/{$year}");
		$start_date = date('Y-m-d', $now);
		$end_date = date('Y-m-d',  strtotime("+1 Month", $now));
		
		$strsql = "
			select 
				{$this->entry_pull_cols}
			from 
				site_entries
			where 
				site_id = ?
				and parent = ? 
				and entry_type = 2 
				and active = 1 
				and {$this->ver_field} > 0 
				and post_date >= ? 
				and post_date <= ?
			order by post_date desc, id desc
		";

		//-------------------------------------------------
		// Pull Entries, Polish and Return
		//-------------------------------------------------
		$entries = qdb_exec($this->data_source, $strsql, array('iiss', $this->site_id, $this->id, $start_date, $end_date));
		$this->polish_entries($entries);
		return $entries;
	}

	//******************************************************************
	// Get Number of Folder Entries by Category Function
	//******************************************************************
	public function get_number_folder_entries_by_category($cat_id=0)
	{
		$cat_id += 0;
		$strsql = "
			select 
				count(*) as count
			from 
				site_entries 
			where 
				site_id = ? 
				and parent = ? 
				and entry_type = 2 
				and active = 1 
				and {$this->ver_field} > 0 
				and cat_id = ? 
		";

		//-------------------------------------------------
		// Pull Entries and return them
		//-------------------------------------------------
		return qdb_lookup($this->data_source, $strsql, 'count', array('iii', $this->site_id, $this->id, $cat_id));
	}

	//******************************************************************
	// Get Folder Entries by Category Function
	//******************************************************************
	public function get_folder_entries_by_category($cat_id=0)
	{
		$cat_id += 0;
		$strsql = "
			select 
				{$this->entry_pull_cols}
			from 
				site_entries 
			where 
				site_id = ? 
				and parent = ? 
				and entry_type = 2 
				and active = 1 
				and {$this->ver_field} > 0 
				and cat_id = ? 
			order by post_date desc, id desc
		";
		
		//-------------------------------------------------
		// Pull Entries, Polish and Return
		//-------------------------------------------------
		$entries = qdb_exec($this->data_source, $strsql, array('iii', $this->site_id, $this->id, $cat_id));
		$this->polish_entries($entries);
		return $entries;
	}

	//******************************************************************
	// Polish Entries Function
	//******************************************************************
	protected function polish_entries(&$entries)
	{
		if (!$entries) { return false; }

		//-------------------------------------------------
		// Pull reference data
		//-------------------------------------------------
		$site_cats = $this->get_all_folder_categories();
		$site_authors = $this->get_site_authors();

		//-------------------------------------------------
		// Polish Entries
		//-------------------------------------------------
		foreach ($entries as $key => $ent) {

			//-------------------------------------------------
			// Categories
			//-------------------------------------------------
			if (isset($site_cats[$ent['cat_id']])) {
				$entries[$key]['category'] = $site_cats[$ent['cat_id']]['category'];
			}
			else {
				$entries[$key]['category'] = '';
			}

			//-------------------------------------------------
			// Authors
			//-------------------------------------------------
			if (isset($site_authors[$ent['entry_author']])) {
				$entries[$key]['author_name'] = $site_authors[$ent['entry_author']]['author_name'];
			}
			else {
				$entries[$key]['author_name'] = '';
			}

		}
	}

	//********************************************************************************
	//********************************************************************************
	// Category Functions
	//********************************************************************************
	//********************************************************************************

	//******************************************************************
	// Get Used Folder Categories Function
	//******************************************************************
	public function get_used_folder_categories()
	{
		$strsql = "
			select 
				cat_id, 
				count(*) as count
			from 
				site_entries
			where 
				site_id = ? 
				and parent = ?
				and entry_type = 2 
				and active = 1 
				and {$this->ver_field} > 0 
			group by cat_id
		";

		//-------------------------------------------------
		// Pull Categories for this folder
		//-------------------------------------------------
		$folder_cats = qdb_exec($this->data_source, $strsql, array('ii', $this->site_id, $this->id), 'cat_id');

		//-------------------------------------------------
		// Pull all Folder Categories
		//-------------------------------------------------
		$site_cats = $this->get_all_folder_categories();
		
		foreach ($folder_cats as $key => $cat) {
			if ($cat['cat_id'] == 0) {
				$folder_cats[$key]['category'] = '';
			}
			else if (isset($site_cats[$key])) {
				$folder_cats[$key]['category'] = $site_cats[$key]['category'];
			}
			else { unset($folder_cats[$key]); }
		}
		
		return $folder_cats;
	}

	//******************************************************************
	// Get All Folder Categories Function
	//******************************************************************
	public function get_all_folder_categories($active=2)
	{
		$active += 0;
		$strsql = "
			select
				id,
				category,
				active 
			from 
				site_entry_cats
			where 
				site_id = ?
				and folder_id = ? ";
		$params = array('ii', $this->site_id, $this->id);
		if ($active == 0 || $active == 1) {
			$strsql .= "and active = ? ";
			$params[0] .= 'i';
			$params[] = $active;
		}
		$strsql .= "
			order by category
		";
		return qdb_exec($this->data_source, $strsql, $params, 'id');
	}

	//******************************************************************
	// Get Folder Category Function
	//******************************************************************
	public function get_folder_category_info($cat_id=0)
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

		$cats = qdb_exec($this->data_source, $strsql, array('iii', $this->site_id, $this->id, $cat_id));
		return (isset($cats[0])) ? ($cats[0]) : (false);
	}


	//********************************************************************************
	//********************************************************************************
	// Author Functions
	//********************************************************************************
	//********************************************************************************

	//******************************************************************
	// Get Folder Authors Function
	//******************************************************************
	public function get_folder_authors()
	{
		$strsql = "
			select 
				entry_author, 
				count(*) as count
			from 
				site_entries
			where 
				site_id = ? 
				and parent = ?
				and entry_type = 2 
				and active = 1 
				and {$this->ver_field} > 0 
			group by entry_author
		";

		//-------------------------------------------------
		// Pull Authors for this folder
		//-------------------------------------------------
		$folder_authors = qdb_exec($this->data_source, $strsql, array('ii', $this->site_id, $this->id), 'entry_author');

		//-------------------------------------------------
		// Pull all Site Authors
		//-------------------------------------------------
		$site_authors = $this->get_site_authors();
		
		foreach ($folder_authors as $key => $author) {
			if ($author['entry_author'] == 0) {
				$folder_authors[$key]['author_name'] = '';
			}
			else if (isset($site_authors[$key])) {
				$folder_authors[$key]['author_name'] = $site_authors[$key]['author_name'];
			}
			else { unset($folder_authors[$key]); }
		}
		
		return $folder_authors;
	}

	//******************************************************************
	// Get Site Authors Function
	//******************************************************************
	public function get_site_authors()
	{
		$strsql = "
			select
				id,
				blog_id,
				author_name 
			from 
				site_blog_authors
			where 
				site_id = ? 
			order by author_name
		";

		$params = array('i', $this->site_id);
		return qdb_exec($this->data_source, $strsql, $params, 'id');
	}

	//******************************************************************
	// Get Author Info Function
	//******************************************************************
	public function get_author_info($author_id=0)
	{
		settype($author_id, 'int');
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

		$params = array('ii', $this->site_id, $author_id);
		return qdb_first_row($this->data_source, $strsql, $params);
	}

}

