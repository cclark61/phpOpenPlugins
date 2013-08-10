<?php
//*****************************************************************************
//*****************************************************************************
/**
* Remote Connectivity Plugin
*
* @package		phpOpenPlugins
* @subpackage	Remote
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenplugins/
* @version 		Started: 1/1/2011, Last updated: 11/17/2011
**/
//*****************************************************************************
//*****************************************************************************

//=============================================================================
//=============================================================================
// Service Availability Function
//=============================================================================
//=============================================================================
function is_service_available($server, $port, $args=false)
{
	// Default NMAP executable location
	$nmap_exec='/usr/bin/nmap';
	$max_to = '2000ms';
	$init_to = '2000ms';

	// Arguments	
	if (is_array($args)) { extract($args); }

    $cmd = "{$nmap_exec} -oX - --open --max-rtt-timeout {$max_to} --initial-rtt-timeout {$init_to} -PN -p {$port} {$server}";
    ob_start();
    passthru($cmd);
    $nmap_xml = ob_get_clean();

	// Set open states
	$open_states = array();
	$open_states['open'] = 'open';
	$open_states['open|filtered'] = 'open|filtered';
	$open_states['unfiltered'] = 'unfiltered';

	if (trim($nmap_xml) != '') {
	    $sxe = new SimpleXMLElement($nmap_xml);
		if (!$sxe) { return false; }
		if (!$sxe->host->ports) { return false; }
		else { $scan_kids = $sxe->host->ports->children(); }

	    $nodeName = $scan_kids->getName();
	
		if ($nodeName == 'port') {
			$sk_attrs = $scan_kids->attributes();
			$port_id = $sk_attrs->portid;
			if ($port_id == $port) {
				$sk_state_attrs = $scan_kids->port->state->attributes();
				$state = (string)$sk_state_attrs->state;
				
				if (isset($open_states[$state])) { return true; }
			}
		}
	}

	return false;
}

?>