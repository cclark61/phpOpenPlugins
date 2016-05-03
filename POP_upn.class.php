<?php
//*****************************************************************************
//*****************************************************************************
/**
* Universal Path Notation (UPN) Plugin
*
* @package		phpOpenPlugins
* @subpackage	Data Access
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 5-1-2016, Last updated: 5-3-2016
**/
//*****************************************************************************
//*****************************************************************************
require_once('POP_static_core.class.php');

//*******************************************************************************
//*****************************************************************************
// POP UPN Class
//*******************************************************************************
//*****************************************************************************
class POP_upn
{
    //=========================================================================
    //=========================================================================
    // Main UPN Handler Method
    //=========================================================================
    //=========================================================================
    public static function _()
    {
        //----------------------------------------------------
        // Process Arguments
        //----------------------------------------------------
        $args = self::ProcessArgs(func_get_args());
        if (!$args) { return false; }
        if (is_array($args)) { extract($args); }
        else { return false; }

        //----------------------------------------------------
        // Process Path Parts
        //----------------------------------------------------
        foreach ($path_parts as $part) {
            if ($mode == 'set') {
                return false;
            }
            //----------------------------------------------------
            // Get Data Element
            //----------------------------------------------------
            else {
                return null;
            }
        }
    }

    //=========================================================================
    //=========================================================================
    // Process Arguments
    //=========================================================================
    //=========================================================================
    protected static function ProcessArgs($args)
    {
        //----------------------------------------------------
        // Pull / Set Args
        //----------------------------------------------------
        $num_args = count($args);
        $args_0 = (!empty($args[0])) ?: (false);
        $args_1 = (!empty($args[1])) ?: (false);
        $args_2 = (!empty($args[2])) ?: (false);

        //----------------------------------------------------
        // Valid Data Element
        //----------------------------------------------------
        if ($args_0 == '') {
            trigger_error('UPN path not given.');
            return false;
        }
        $full_upn = $args_0;

        //----------------------------------------------------
        // Get Handler
        //----------------------------------------------------
        $upn_parts = explode(':', $full_upn);
        if (count($upn_parts) < 2) {
            trigger_error('Invalid UPN Path.');
            return false;
        }
        $handler = $upn_parts[0];
        $mode = false;

        //----------------------------------------------------
        // Validate Handler
        //----------------------------------------------------
        switch ($handler) {

            case 'config':
                $mode = ($num_args > 1) ? ('get') ? ('set');
                $subject = (isset($_SESSION['config'])) ?: (false);
                break;

            case 'json':
                $mode = ($num_args > 2) ? ('get') ? ('set');
                $subject = json_decode($args_2);
                break;

            case 'array':
                $mode = ($num_args > 2) ? ('get') ? ('set');
                $subject = (is_array($args_2)) ?: (false);
                break;

            case 'session':
            case 'post':
            case 'get':
            case 'request'
            case 'server':
            case 'globals':
                $mode = ($num_args > 1) ? ('get') ? ('set');
                switch () {
                    case 'session':
                        $subject =& $_SESSION;
                        break;
                    case 'post':
                        $subject =& $_POST;
                        break;
                    case 'get':
                        $subject =& $_GET;
                        break;
                    case 'request'
                        $subject =& $_REQUEST;
                        break;
                    case 'server':
                        $subject =& $_SERVER;
                        break;
                    case 'globals':
                        $subject =& $GLOBALS;
                        break;
                }
                break;

            default:
                trigger_error('Unknown UPN path type.');
                return false;
                break;

        }

        //----------------------------------------------------
        // Get Path Parts
        //----------------------------------------------------
        $path_parts = explode('/', $upn_parts[1]);

        //----------------------------------------------------
        // Return Data
        //----------------------------------------------------
        return [
            'full_upn' => $full_upn,
            'handler' => $handler,
            'path_parts' => $path_parts,
            'mode' => $mode,
            'subject' => $subject
        ];
    }

    //=========================================================================
    //=========================================================================

}

