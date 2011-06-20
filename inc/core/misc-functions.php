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
	$string = strip_tags($string);

	/** Short-circuit if no shortening is needed */
	if(!isset($string[$length + 1])) return $string;

	// By default, an ellipsis will be appended to the end of the text.
	$suffix = '...';

	// Convert 'smart' punctuation to 'dumb' punctuation, strip the HTML tags,
	// and convert all tabs and line-break characters to single spaces.
	$short_desc = trim(str_replace(array("\r","\n", "\t"), ' ', $string));

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
 * Reset the system timezone to UTC
 */
function timezone_default() {
	date_default_timezone_set('UTC');
}

/**
 * Apply a timezone offset to a Unix timestamp
 *
 * @param int $timestamp
 * @return int
 */
function timezone_apply($timestamp) {
	$timezone = get_option('timezone', 'UTC');
	return $timestamp + ( timezone_get_gmt_offset($timezone) * 3600);
}

/**
 * Calculate a GMT offset from a zoneinfo timezone string
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

/**
 * Generate a nonce
 *
 * Nonces are used to avoid CSRFs, by generating a time-, user- and
 * action-dependent token.
 *
 * The implementation of these nonces is based on WordPress' implementation.
 *
 * @param string $action The current action taking place
 * @return string Nonce string
 */
function generate_nonce($action) {
	$user_settings = get_option('auth');
	$time = ceil(time() / 43200);
	return sha1($time . $action . $user_settings['user']);
}

/**
 * Check validity of submitted nonce
 *
 * @param string $action The current action taking place
 * @param string $nonce Supplied nonce
 * @return bool True if nonce is equal, false if not
 */
function check_nonce($action, $nonce) {
	$user_settings = get_option('auth');
	$time = ceil(time() / 43200);
	$current_nonce = sha1($time . $action . $user_settings['user']);
	if ($nonce === $current_nonce) {
		return true;
	}
	$old_nonce = sha1(($time - 1) . $action . $user_settings['user']);
	if ($nonce === $old_nonce) {
		return true;
	}
	return false;
}
?>