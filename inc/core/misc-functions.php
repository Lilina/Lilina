<?php
/**
 * @todo Document
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
defined('LILINA') or die('Restricted access');

/**
 * Fixes the $_SERVER['REQUEST_URI'] variable on IIS
 * @
 */
function fix_request_uri() {
	// Fix for IIS, which doesn't set REQUEST_URI
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {

		// IIS Mod-Rewrite
		if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
			$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
		}
		// IIS Isapi_Rewrite
		else if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
			$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
		}
		else {
			// If root then simulate that no script-name was specified
			if (empty($_SERVER['PATH_INFO']))
				$_SERVER['REQUEST_URI'] = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')) . '/';
			elseif ( $_SERVER['PATH_INFO'] == $_SERVER['SCRIPT_NAME'] )
				// Some IIS + PHP configurations puts the script-name in the path-info (No need to append it twice)
				$_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
			else
				$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];

			// Append the query string if it exists and isn't null
			if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
				$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
			}
		}
	}
}

/**
 * @todo Document
 */
function lilina_timer_start() {
	//Start measuring execution time
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$starttime = $mtime;
	return $starttime;
}
/**
 * @todo Document
 */
function lilina_timer_end($starttime) {
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$endtime = $mtime;
	$totaltime = ($endtime - $starttime);
	$totaltime = round($totaltime, 2);
	return $totaltime;
}
?>