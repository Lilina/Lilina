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
 * lilina_load_feeds() - {{@internal Missing Short Description}}}
 *
 * {{@internal Missing Long Description}}}
 */
function lilina_load_feeds($data_file) {
	$data = file_get_contents($data_file) ;
	$data = unserialize( base64_decode($data) ) ;
	if(!$data || !is_array($data)) {
		$data = array();
	}
	return $data;
}

/**
 * available_templates() - {{@internal Missing Short Description}}}
 *
 * {{@internal Missing Long Description}}}
 */
function available_templates() {
	//Make sure we open it correctly
	if ($handle = opendir(LILINA_INCPATH . '/templates/')) {
		//Go through all entries
		while (false !== ($dir = readdir($handle))) {
			// just skip the reference to current and parent directory
			if ($dir != '.' && $dir != '..') {
				if (is_dir(LILINA_INCPATH . '/templates/' . $dir)) {
					if(file_exists(LILINA_INCPATH . '/templates/' . $dir . '/style.css')) {
						$list[] = $dir;
					}
				} 
			}
		}
		// ALWAYS remember to close what you opened
		closedir($handle);
	}
	foreach($list as $the_template) {
		$temp_data = implode('', file(LILINA_INCPATH . '/templates/' . $the_template . '/style.css'));
		preg_match("|Name:(.*)|i", $temp_data, $real_name);
		preg_match("|Description:(.*)|i", $temp_data, $desc);
		$templates[]	= array(
								'name' => $the_template,
								'real_name' => trim($real_name[1]),
								'description' => trim($desc[1])
								);
	}
	return $templates;
}

/**
 * available_locales() - {{@internal Missing Short Description}}}
 *
 * {{@internal Missing Long Description}}}
 */
function available_locales() {
	$locale_list = array();
	$locales = array();
	//Make sure we open it correctly
	if ($handle = opendir(LILINA_INCPATH . '/locales/')) {
		//Go through all entries
		while (false !== ($file = readdir($handle))) {
			// just skip the reference to current and parent directory
			if ($file != '.' && $file != '..') {
				if (!is_dir(LILINA_INCPATH . '/locales/' . $file)) {
					//Only add plugin files
					if(strpos($file,'.mo') !== FALSE) {
						$locale_list[] = $file;
					}
				}
			}
		}
		// ALWAYS remember to close what you opened
		closedir($handle);
	}
	/** Special case for English */
	$locales[]	= array('name' => 'en',
						'file' => '');
	foreach($locale_list as $locale) {
		echo $locale;
		//Quick and dirty name
		$locales[]	= array('name' => str_replace('.mo', '', $locale),
							'file' => $locale);
	}
	return $locales;
}

/**
 * recursive_array_code() - {{@internal Missing Short Description}}}
 *
 * {{@internal Missing Long Description}}}
 */
function recursive_array_code($vars) {
	global $level_count;
	foreach($vars as $var => $value) {
		if(is_array($value)) {
			$content .= "\n" . str_repeat("\t", $level_count) . 'array(';
			$level_count++;
			$content .= recursive_array_code($value);
		}
		else
			$content .= "\n" . str_repeat("\t", $level_count) . "'$var' => '$value',";
	}
	while($level_count > 1) {
		$level_count--;
		$content .= "\n" . str_repeat("\t", $level_count) . '),';
	}
	return $content;
}

/**
 * save_settings() - {@internal Missing Short Description}}
 *
 *
 */
function save_settings() {
	global $options;
	$data = new DataHandler(LILINA_CONTENT_DIR . '/system/config/');
	return $data->save('options.data', serialize($options));
}

/**
 * generate_nonce() - Generates nonce
 *
 * Uses the current time
 * @global array Need settings for user and password
 * @param string $nonce Supplied nonce
 * @return bool True if nonce is equal, false if not
 */
function generate_nonce() {
	$user_settings = get_option('auth');
	$time = ceil(time() / 43200);
	return md5($time . get_option('auth', 'user') . get_option('auth', 'pass'));
}

/**
 * check_nonce() - Checks whether supplied nonce matches current nonce
 * @global array Need settings for user and password
 * @param string $nonce Supplied nonce
 * @return bool True if nonce is equal, false if not
 */
function check_nonce($nonce) {
	$user_settings = get_option('auth');
	$time = ceil(time() / 43200);
	$current_nonce = md5($time . get_option('auth', 'user') . get_option('auth', 'pass'));
	if($nonce !== $current_nonce) {
		return false;
	}
	return true;
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
?>