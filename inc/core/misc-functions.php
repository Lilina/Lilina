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
 * @author WordPress
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
 * Checks to see if a new version of Lilina is available
 * @author WordPress
 */
function lilina_version_check() {
	if ( !function_exists('fsockopen') || strpos($_SERVER['PHP_SELF'], 'install.php') !== false || defined('LILINA_INSTALLING') || !is_admin() )
		return;

	global $lilina, $settings;
	//Just to make sure
	require_once(LILINA_INCPATH . '/core/version.php');
	require_once(LILINA_INCPATH . '/core/conf.php');
	$lilina_version = $lilina['core-sys']['version'];
	$php_version = phpversion();

	$current = get_option('update_status');
	$locale = get_option('lang');

	if (
		isset( $current->last_checked ) &&
		43200 > ( time() - $current->last_checked ) &&
		$current->version_checked == $lilina_version
	)
		return false;

	$new_option = '';
	$new_option->last_checked = time(); // this gets set whether we get a response or not, so if something is down or misconfigured it won't delay the page load for more than 3 seconds, twice a day
	$new_option->version_checked = $lilina_version;

	$http_request  = "GET /version-check/lilina-core/?version=$lilina_version&php=$php_version&locale=$locale HTTP/1.0\r\n";
	$http_request .= "Host: getlilina.org\r\n";
	//$http_request .= 'Content-Type: application/x-www-form-urlencoded; charset=' . get_option('blog_charset') . "\r\n";
	$http_request .= 'Content-Type: application/x-www-form-urlencoded; charset=' . $settings['encoding'] . "\r\n";
	$http_request .= 'User-Agent: Lilina/'. $lilina_version .';  ' . $settings['baseurl'] . "\r\n";
	$http_request .= "\r\n";

	$response = '';
	if ( false !== ( $fs = @fsockopen( 'getlilina.org', 80, $errno, $errstr, 3 ) ) && is_resource($fs) ) {
		fwrite( $fs, $http_request );
		while ( !feof( $fs ) )
			$response .= fgets( $fs, 1160 ); // One TCP-IP packet
		fclose( $fs );

		$response = explode("\r\n\r\n", $response, 2);
		$body = trim( $response[1] );
		$body = str_replace(array("\r\n", "\r"), "\n", $body);

		$returns = explode("\n", $body);

		$new_option->response = $returns[0];
		if ( isset( $returns[1] ) )
			$new_option->url = $returns[1];
	}
	update_option('update_status', $new_option );
}
register_action('init', 'lilina_version_check');

/**
 * @todo Document
 */
function lilina_footer_version() {
	global $lilina;
	$cur = get_option('update_status');
	if(!is_admin() || !is_object($cur)) {
		echo $lilina['core-sys']['version'];
	}

	switch ( $cur->response ) {
		case 'development' :
			printf(' | '.__( 'You are using a development version (%s). Thanks! Make sure you <a href="%s">stay updated</a>.' ), $lilina['core-sys']['version'], 'http://getlilina.org/download/#svn');
		break;

		case 'upgrade' :
			printf(' | <strong>'.__( 'Your installation of Lilina (%s) is out of date. <a href="%s">Please update</a>.' ).'</strong>', $lilina['core-sys']['version'], $cur->url);
		break;

		case 'latest' :
		default :
			printf(' | '.__( 'Version %s' ), $lilina['core-sys']['version']);
		break;
	}
}
register_action('admin_footer', 'lilina_footer_version');

/**
 * @todo Document
 * @author WordPress
 */
function update_nag() {
	$cur = get_option( 'update_status' );

	if ( ! isset( $cur->response ) || $cur->response != 'upgrade' )
		return false;

	$msg = sprintf(_r('A new version of Lilina is available! <a href="%s">Please update now</a>.'), $cur->url);

	echo "<div id='update-nag'>$msg</div>";
}
add_action('admin_notices', 'update_nag');

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

function is_admin() {
	if(defined('LILINA_ADMIN') && LILINA_ADMIN == true) {
		return true;
	}
	return false;
}

/**
 * Checks differences between 2 arrays recursively
 *
 * Like running array_diff_assoc, except recursive and PHP <4.3.0 compatible
 * From the user contributed notes for array_diff_assoc at PHP.net
 * @author chinello at gmail dot com
 * @link http://au.php.net/manual/en/function.array-diff-assoc.php
 */
function array_diff_assoc_recursive($array1, $array2) {
	foreach($array1 as $key => $value) {
		if(is_array($value)) {
			  if(!isset($array2[$key])) {
				  $difference[$key] = $value;
			  }
			  elseif(!is_array($array2[$key])) {
				  $difference[$key] = $value;
			  }
			  else {
				  $new_diff = array_diff_assoc_recursive($value, $array2[$key]);
				  if($new_diff != FALSE) {
						$difference[$key] = $new_diff;
				  }
			  }
		  }
		  elseif(!isset($array2[$key]) || $array2[$key] != $value) {
			  $difference[$key] = $value;
		  }
	}
	return !isset($difference) ? 0 : $difference;
}

/**
 * Sets setting <tt>$option</tt> to <tt>$new_value<tt>
 *
 * This exists for when we want to introduce MySQL capability
 * @global array <tt>$settings</tt> contains whatever option we are going to change
 * @param string $option Option key to change
 * @param mixed $new_value New value to set <tt>$option</tt> to
 */
function update_option($option, $new_value) {
	global $settings;
	$settings[$option] = $new_value;
}

/**
 * Gets value of setting <tt>$option</tt>
 *
 * This exists for when we want to introduce MySQL capability
 * @global array <tt>$settings</tt> contains whatever option we are getting
 * @param string $option Option key to get
 */
function get_option($option) {
	global $settings;
	if(!isset($settings[$option])) {
		return false;
	}
	return $settings[$option];
}
?>