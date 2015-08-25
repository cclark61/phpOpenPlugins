<?php
//*****************************************************************************
//*****************************************************************************
/**
* MongoDB Functions Plugin
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

//*******************************************************************************
//*******************************************************************************
// POP MongoDB Object
//*******************************************************************************
//*******************************************************************************
class POP_mongodb
{

	//*****************************************************************************
	//****************************************************************************
	// Stream MongoDB Image
	//*****************************************************************************
	//*****************************************************************************
	public static function stream_gridfs_file($gridfs, $id, $args=false)
	{
		if (is_array($args)) { extract($args); }

		//*****************************************************************
		// Try to Get File Record from MongoDB
		//*****************************************************************
		$mongo_file = false;
		try {
			$mongo_file = $gridfs->get(new MongoId($id));
		}
		catch (Exception $e) {
			if (!empty($show_errors)) { trigger_error($e); }
			return false;
		}
		
		//*****************************************************************
		// Valid MongoDB File?
		//*****************************************************************
		if (!$mongo_file) { return false; }
		
		//*****************************************************************
		// Output Content Type / Content
		//*****************************************************************
		$stream = true;
		if (!empty($output_header)) {
			$stream = POP_cdn::output_content_type($mongo_file->getFilename());
		}
		if ($stream) {
			print $mongo_file->getBytes();
		}
		else {
			return false;
		}

		return true;
	}


}

