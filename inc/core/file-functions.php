<?php
/**
 * Functions that work with serialized files
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

defined('LILINA_PATH') or die('Restricted access');

/**
 * Save options to options.data
 */
function save_options() {
	global $options;
	$data = new DataHandler(LILINA_CONTENT_DIR . '/system/config/');
	return $data->save('options.data', serialize($options));
}

/**
 * get_temp_dir() - Get a temporary directory to try writing files to
 *
 * {@internal Missing Long Description}}
 * @author WordPress
 */
function get_temp_dir() {
	if ( defined('LILINA_TEMP_DIR') )
		return trailingslashit(LILINA_TEMP_DIR);

	$temp = LILINA_PATH . '/content/system/temp';
	if ( is_dir($temp) && is_writable($temp) )
		return $temp;

	if  ( function_exists('sys_get_temp_dir') )
		return trailingslashit(sys_get_temp_dir());

	return '/tmp/';
}

/**
 * File validates against allowed set of defined rules.
 *
 * A return value of '1' means that the $file contains either '..' or './'. A
 * return value of '2' means that the $file contains ':' after the first
 * character. A return value of '3' means that the file is not in the allowed
 * files list.
 *
 * @since 1.2.0
 * @author WordPress
 *
 * @param string $file File path.
 * @param array $allowed_files List of allowed files.
 * @return int 0 means nothing is wrong, greater than 0 means something was wrong.
 */
function validate_file( $file, $allowed_files = '' ) {
	if ( false !== strpos( $file, '..' ))
		return 1;

	if ( false !== strpos( $file, './' ))
		return 1;

	if (':' == substr( $file, 1, 1 ))
		return 2;

	if (!empty ( $allowed_files ) && (!in_array( $file, $allowed_files ) ) )
		return 3;

	return 0;
}

/**
 * Delete all cached HTML pages from the CacheHandler class
 *
 * @return bool
 */
function clear_html_cache() {
	$files = glob(get_option('cachedir') . '*.cache');
	foreach($files as $file) {
		if(!unlink($file)) {
			return false;
		}
	}
}
?>