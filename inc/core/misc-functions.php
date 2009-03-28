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
 * Fix the $_SERVER['REQUEST_URI'] variable on IIS.
 *
 * IIS does not set the $_SERVER['REQUEST_URI'] variable, so we need to generate it for it
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
 * "Start" the "timer".
 *
 * Runs the lilina_timer_start action then returns the current timestamp
 * which is used as a pseudo-timer.
 * @see lilina_timer_end()
 * @return float
 */
function lilina_timer_start() {
	do_action('lilina_timer_start');
	return microtime(true);
}
/**
 * "End" the "timer".
 *
 * Runs the lilina_timer_end action then returns the difference between the
 * current timestamp and the supplied one. The supplied timestamp is usually
 * the output of a {@link lilina_timer_start()}, as this simulates a timer.
 *
 * @see lilina_timer_start()
 * @param float $starttime Timestamp returned by lilina_timer_start()
 * @return float Difference between $starttime and the current timestamp
 */
function lilina_timer_end($starttime) {
	$endtime = microtime(true);
	$totaltime = ($endtime - $starttime);
	$totaltime = round($totaltime, 2);
	do_action('lilina_timer_end');
	return $totaltime;
}
/**
 * Check if we're on an admin page.
 *
 * Checks the LILINA_ADMIN constant to see if we're currently on an
 * administration page. Note: this does not check if the user is an admin, it
 * simply checks if the page is in the administration
 *
 * @return bool
 */
function is_admin() {
	if(defined('LILINA_ADMIN') && LILINA_ADMIN == true) {
		return true;
	}
	return false;
}

/**
 * Update the value of an option.
 *
 * If the option does not exist, then the option will be added with the option
 * value, but you will not be able to set whether it is autoloaded. If you want
 * to set whether an option autoloaded, then you need to use the add_option().
 * Any of the old $settings keys are ignored.
 *
 * When the option is updated, then the filter named
 * 'update_option_$option_name', with the $option_name as the $option_name
 * parameter value, will be called. The hook should accept two parameters, the
 * first is the new parameter and the second is the old parameter.
 *
 * @global array <tt>$settings</tt> contains whatever option we are going to change
 * @param string $option Option key to change
 * @param mixed $new_value New value of <tt>$option</tt>
 */
function update_option($option_name, $new_value) {
	if($option_name === 'auth' || $option_name === 'sitename' || $option_name === 'baseurl' || $option_name === 'files')
		return false;

	global $options;
	$options[$option_name] = apply_filters("update_option-$option_name", $new_value);
	return save_options();
}

/**
 * Retrieve option value based on setting name.
 *
 * If the option does not exist or does not have a value, then the return value
 * will be false. This is useful to check whether you need to install an option
 * and is commonly used during installation of plugin options and to test
 * whether upgrading is required.
 *
 * There is a filter called 'option_$option' with the $option being replaced
 * with the option name. This gives the value as the only parameter.
 *
 * @uses $settings Old settings array for "auth", "sitename", "baseurl" and "files" options.
 * @uses $options New options array
 * @param string $option Name of option to retrieve.
 * @param mixed $default Value to default to if none is found. Alternatively used as a "subkey" option for the hardcoded settings
 * @return mixed Value set for the option.
 */
function get_option($option, $default = null) {
	global $settings;
	
	/** Hardcoded settings in settings.php */
	if($option === 'auth' || $option === 'sitename' || $option === 'baseurl' || $option === 'files') {
		if(!isset($settings[$option]))
			return false;
		
		if($default) {
			if(!isset($settings[$option][$default]))
				return false;
			return $settings[$option][$default];
		}
		return $settings[$option];
	}

	/** New-style options in options.data */
	global $options;
	if(!isset($options[$option]))
		return $default;

	return $options[$option];
}

/**
 * Merge user defined arguments into defaults array.
 *
 * This function is used throughout Lilina to allow for both string or array
 * to be merged into another array.
 *
 * @author WordPress
 * @param string|array $args Value to merge with $defaults
 * @param array $defaults Array that serves as the defaults.
 * @return array Merged user defined values with defaults.
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
 * Parses a string into variables to be stored in an array.
 *
 * Uses {@link http://www.php.net/parse_str parse_str()} and stripslashes if
 * {@link http://www.php.net/magic_quotes magic_quotes_gpc} is on.
 *
 * @author WordPress
 * @uses apply_filters() for the 'lilina_parse_str' filter.
 *
 * @param string $string The string to be parsed.
 * @param array $array Variables will be stored in this array.
 */
function lilina_parse_str( $string, &$array ) {
	parse_str( $string, $array );
	if ( get_magic_quotes_gpc() )
		$array = stripslashes_deep( $array ); // parse_str() adds slashes if magicquotes is on.  See: http://php.net/parse_str
	$array = apply_filters( 'lilina_parse_str', $array );
}

/**
 * Navigates through an array and removes slashes from the values.
 *
 * If an array is passed, the array_map() function causes a callback to pass the
 * value back to the function. The slashes from this value will removed.
 *
 * @author WordPress
 *
 * @param array|string $value The array or string to be striped.
 * @return array|string Stripped array (or string in the callback).
 */
function stripslashes_deep($value) {
	 $value = is_array($value) ?
		 array_map('stripslashes_deep', $value) :
		 stripslashes($value);

	 return $value;
}

/**
 * Navigates through an array and encodes the values to be used in a URL.
 *
 * Uses a callback to pass the value of the array back to the function as a
 * string.
 *
 * @author WordPress
 *
 * @param array|string $value The array or string to be encoded.
 * @return array|string $value The encoded array (or string from the callback).
 */
function urlencode_deep($value) {
	 $value = is_array($value) ?
		 array_map('urlencode_deep', $value) :
		 urlencode($value);

	 return $value;
}

/**
 * Unserialize value only if it was serialized.
 *
 * @author WordPress
 *
 * @param string $original Maybe unserialized original, if is needed.
 * @return mixed Unserialized data can be any type.
 */
function maybe_unserialize( $original ) {
	if ( is_serialized( $original ) ) // don't attempt to unserialize data that wasn't serialized going in
		if ( false !== $gm = @unserialize( $original ) )
			return $gm;
	return $original;
}

/**
 * Check value to find if it was serialized.
 *
 * If $data is not an string, then returned value will always be false.
 * Serialized data is always a string.
 *
 * @author WordPress
 *
 * @param mixed $data Value to check to see if was serialized.
 * @return bool False if not serialized and true if it was.
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
 * Turn register globals off.
 *
 * @access private
 * @since 2.1.0
 * @return null Will return null if register_globals PHP directive was disabled
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
 * Cut a specified string down to $length characters
 *
 * Removes all HTML tags (not entities), shortens to $length characters and
 * returns the new string with an elipsis (plain text ...) appended
 *
 * @author <http://simplepie.org/wiki/tutorial/shorten_titles_and_descriptions>
 * @param string $string String to shorten
 * @param int length Length to shorten to (in characters)
 * @return string Shortened string
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

/**
 * Resets the system timezone to UTC
 */
function timezone_default() {
	date_default_timezone_set('UTC');
}

/**
 * Applies a timezone offset to a Unix timestamp
 *
 * @param int $timestamp
 * @return int
 */
function timezone_apply($timestamp) {
	$timezone = get_option('timezone', 'UTC');
	return $timestamp + ( timezone_get_gmt_offset($timezone) * 3600);
}

/**
 * Calculates a GMT offset from a zoneinfo timezone string
 *
 * @param string|int $timezone Zone to calculate offset from
 * @return bool|int Offset from
 */
function timezone_get_gmt_offset($timezone) {
	if (empty($timezone))
		return false;

	/** If we're supplied with an integer, assume it's a GMT offset */
	if (is_int($timezone))
		return $timezone;

	if (class_exists('DateTime')) {
		$dtz = new DateTimeZone($timezone);
		$dt = new DateTime();
		$offset = $dtz->getOffset($dt);
		// convert to hours
		$offset = $offset / 3600;
		return $offset;
	}
}
?>