<?php
/**
 * @todo Document
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
defined('LILINA_PATH') or die('Restricted access');

/**
 * lilina_fix_request_uri() - Fixes the $_SERVER['REQUEST_URI'] variable on IIS
 *
 * {@internal Missing Long Description}}
 * @author WordPress
 */
function lilina_fix_request_uri() {
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
 * lilina_timer_start() - {@internal Missing Short Description}
 *
 * {@internal Missing Long Description}}
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
 * lilina_timer_end() - {@internal Missing Short Description}
 *
 * {@internal Missing Long Description}}
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
/**
 * is_admin() - {@internal Missing Short Description}
 *
 * {@internal Missing Long Description}}
 */
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
	save_settings();
}

/**
 * Gets value of setting <tt>$option</tt>
 *
 * This exists for when we want to introduce MySQL capability
 * @global array <tt>$settings</tt> contains whatever option we are getting
 * @param string $option Option key to get
 */
function get_option($option, $suboption = '') {
	global $settings;

	if(!isset($settings[$option]))
		return false;

	if($suboption) {
		if(!isset($settings[$option][$suboption]))
			return false;
		return $settings[$option][$suboption];
	}
	return $settings[$option];
}

/**
 * lilina_parse_args() - {@internal Missing Short Description}
 *
 * {@internal Missing Long Description}}
 * @author WordPress
 */
function lilina_parse_args( $args, $defaults = '' ) {
	if ( is_object( $args ) )
		$r = get_object_vars( $args );
	elseif ( is_array( $args ) )
		$r =& $args;
	else
		lilina_parse_str( $args, $r );

	if ( is_array( $defaults ) )
		return array_merge( $defaults, $r );
	return $r;
}

/**
 * lilina_parse_str() - {@internal Missing Short Description}
 *
 * {@internal Missing Long Description}}
 * @author WordPress
 */
function lilina_parse_str( $string, &$array ) {
	parse_str( $string, $array );
	if ( get_magic_quotes_gpc() )
		$array = stripslashes_deep( $array ); // parse_str() adds slashes if magicquotes is on.  See: http://php.net/parse_str
	$array = apply_filters( 'lilina_parse_str', $array );
}

if(!function_exists('stripslashes_deep')) {
/**
 * stripslashes_deep() - {@internal Missing Short Description}
 *
 * {@internal Missing Long Description}}
 * @author WordPress
 */
function stripslashes_deep($value) {
	 $value = is_array($value) ?
		 array_map('stripslashes_deep', $value) :
		 stripslashes($value);

	 return $value;
}
}

if(!function_exists('urlencode_deep')):

/**
 * urlencode_deep() - {@internal Missing Short Description}
 *
 * {@internal Missing Long Description}}
 * @author WordPress
 */
function urlencode_deep($value) {
	 $value = is_array($value) ?
		 array_map('urlencode_deep', $value) :
		 urlencode($value);

	 return $value;
}

endif; //function_exists('urlencode_deep)

/**
 * maybe_unserialize() - Unserialize data only if it is serialized
 *
 * {@internal Missing Long Description}}
 * @author WordPress
 */
function maybe_unserialize( $original ) {
	if ( is_serialized( $original ) ) // don't attempt to unserialize data that wasn't serialized going in
		if ( false !== $gm = @unserialize( $original ) )
			return $gm;
	return $original;
}

/**
 * is_serialized() - Check if $data is serialized
 *
 * {@internal Missing Long Description}}
 * @author WordPress
 */
function is_serialized( $data ) {
	// if it isn't a string, it isn't serialized
	if ( !is_string( $data ) )
		return false;
	$data = trim( $data );
	if ( 'N;' == $data )
		return true;
	if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
		return false;
	switch ( $badions[1] ) {
		case 'a' :
		case 'O' :
		case 's' :
			if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
				return true;
			break;
		case 'b' :
		case 'i' :
		case 'd' :
			if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
				return true;
			break;
	}
	return false;
}

/**
 * Unregisters globals and reverts magic quotes
 *
 * This looks like ugly code.
 * @author WordPress
 */
function lilina_level_playing_field() {
	lilina_fix_request_uri();
	if (ini_get('register_globals')) {
		if ( isset($_REQUEST['GLOBALS']) )
			die('GLOBALS overwrite attempt detected');

		// Variables that shouldn't be unset
		$keep = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES', 'table_prefix');

		$input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());
		foreach ( $input as $k => $v ) {
			if ( !in_array($k, $keep) && isset($GLOBALS[$k]) ) {
				$GLOBALS[$k] = NULL;
				unset($GLOBALS[$k]);
			}
		}
	}

	if (get_magic_quotes_gpc()) {
		list($_GET, $_POST, $_COOKIE, $_REQUEST) = stripslashes_deep(array($_GET, $_POST, $_COOKIE, $_REQUEST));
	}
}

/**
 * Adds a notice to the top of the page
 *
 * Creates an anonymous function to concatenate $message to the alert_box filter
 * Note: this uses ugly code, but must to avoid escaping double quotes as well
 * @param string $message Notice to add
 */
function add_notice($message) {
	MessageHandler::add($message);
	//add_filter('alert_box', create_function('$text', 'return $text . \'<p>' . str_replace( '\'', '\\\'', ) . '</p>\';'));
}

/**
 * Adds a technical notice to the top of the page
 *
 * Creates an anonymous function to concatenate $message to the alert_box filter
 * Note: this uses ugly code, but must to avoid escaping double quotes as well
 * @param string $message Notice to add
 */
function add_tech_notice($message) {
	MessageHandler::add($message);
	//add_filter('alert_box', create_function('$text', 'return $text . \'<p class="tech_notice"><span class="actual_notice">' . str_replace( '\'', '\\\'', $message) . '</span></p>\';'));
}

/**
 * shorten() - Cut a specified string down to $length characters
 *
 * Removes all HTML tags (not entities), shortens to $length characters and
 * returns the new string with an elipsis (plain text ...) appended
 * @param string $string String to shorten
 * @param int length Length to shorten to (in characters)
 * @return string Shortened string
 * @author <http://simplepie.org/wiki/tutorial/shorten_titles_and_descriptions>
 */
function shorten($string, $length) {
	/** Short-circuit if no shortening is needed */
	if(!isset($string[$length + 1])) return $string;

	// By default, an ellipsis will be appended to the end of the text.
	$suffix = '...';

	// Convert 'smart' punctuation to 'dumb' punctuation, strip the HTML tags,
	// and convert all tabs and line-break characters to single spaces.
	$short_desc = trim(str_replace(array("\r","\n", "\t"), ' ', strip_tags($string)));

	// Cut the string to the requested length, and strip any extraneous spaces 
	// from the beginning and end.
	$desc = trim(substr($short_desc, 0, $length));

	// Find out what the last displayed character is in the shortened string
	$lastchar = substr($desc, -1, 1);

	// If the last character is a period, an exclamation point, or a question 
	// mark, clear out the appended text.
	if ($lastchar == '.' || $lastchar == '!' || $lastchar == '?') $suffix='';

	// Append the text.
	$desc .= $suffix;

	// Send the new description back to the page.
	return $desc;
}

/**
 * Get the path to the data directory
 *
 * @since 1.0
 * @uses get_option()
 *
 * @return string
 */
function get_data_dir() {
	return LILINA_DATA_DIR;
}
?>