<?php
/**
 * Functions that work with serialized files
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

defined('LILINA') or die('Restricted access');
/**
 * @todo Document
 */
function lilina_load_times() {
	global $settings;
	if (file_exists($settings['files']['times'])) {
		$time_table = file_get_contents($settings['files']['times']) ;
		$time_table = unserialize($time_table) ;
	}
	else {
		$time_table = array();
	}
	if(!$time_table || !is_array($time_table)) {
		$time_table = array();
	}
	return $time_table;
}
/**
 * @todo Document
 */
function lilina_save_times($times) {
	global $settings;
	// save times
	$ttime = serialize($times);
	$fp = fopen($settings['files']['times'],'w') ;
	fputs($fp, $ttime) ;
	fclose($fp) ;
}
/**
 * @todo Document
 */
function lilina_load_feeds($data_file) {
	$data = file_get_contents($data_file) ;
	$data = unserialize( base64_decode($data) ) ;
	if(!$data || !is_array($data)) {
		$data = array();
	}
	return $data;
}

function available_templates() {
	//Make sure we open it correctly
	if ($handle = opendir(LILINA_INCPATH . '/templates/')) {
		//Go through all entries
		while (false !== ($dir = readdir($handle))) {
			// just skip the reference to current and parent directory
			if ($dir != '.' && $dir != '..') {
				if (is_dir(LILINA_INCPATH . '/templates/' . $dir)) {
					if(file_exists(LILINA_INCPATH . '/templates/' . $dir . '/style.css')) {
						$list[] = LILINA_INCPATH . '/templates/' . $dir . '/style.css';
					}
				} 
			}
		}
		// ALWAYS remember to close what you opened
		closedir($handle);
	}
	foreach($list as $the_template) {
		$temp_data = implode('', file($the_template));
		preg_match("|Name:(.*)|i", $temp_data, $name);
		preg_match("|Real Name:(.*)|i", $temp_data, $real_name);
		preg_match("|Description:(.*)|i", $temp_data, $desc);
		$templates[]	= array(
								'name' => trim($name[1]),
								'real_name' => trim($real_name[1]),
								'description' => trim($desc[1])
								);
	}
	return $templates;
}

function available_locales() {
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
	foreach($locale_list as $locale) {
	echo $locale;
		//Quick and dirty name
		$locales[]	= array('name' => str_replace('.mo', '', $locale),
							'file' => $locale);
	}
	return $locales;
}


function save_settings() {
	global $settings, $default_settings;
	$raw_php		= "<?php";
	$changed_settings = array_diff_assoc_recursive($settings, $default_settings);
	//Workaround some which aren't needed
	if(isset($changed_settings['cachedir'])) {
		unset($changed_settings['cachedir']);
	}
	if(isset($changed_settings['cachedir'])) {
		unset($changed_settings['cachedir']);
	}
	var_dump($changed_settings);
	/*foreach($setting
\$settings['sitename'] = '$sitename';
\$settings['baseurl'] = '$guessurl';
\$settings['auth'] = array(
							'user' => '$username',
							'pass' => '" . md5($password) . "'
							);
?>";
		$settings_file	= @fopen('./conf/settings.php', 'w+');
		if(!@file_exists('./conf/feeds.data')) {
			$feeds_file = @fopen('./conf/feeds.data', 'w+');
			if($feeds_file) {
				fclose($feeds_file) ;
			}
		}
		if(!@file_exists('./conf/time.data')) {
			$times_file = @fopen('./conf/time.data', 'w+');
			if($times_file) {
				fclose($times_file);
			}
		}
		if($settings_file){
			fputs($settings_file, $raw_php) ;
			fclose($settings_file) ;
	return true;*/
}

/**
 * Generates nonce
 *
 * Uses the current time
 * @global array Need settings for user and password
 * @param string $nonce Supplied nonce
 * @return bool True if nonce is equal, false if not
 */
function generate_nonce() {
	$user_settings = get_option('auth');
	$time = ceil(time() / 43200);
	return md5($time . $user_settings['user'] . $user_settings['pass']);
}

/**
 * Checks whether supplied nonce matches current nonce
 * @global array Need settings for user and password
 * @param string $nonce Supplied nonce
 * @return bool True if nonce is equal, false if not
 */
function check_nonce($nonce) {
	$user_settings = get_option('auth');
	$time = ceil(time() / 43200);
	$current_nonce = md5($time . $user_settings['user'] . $user_settings['pass']);
	if($nonce !== $current_nonce) {
		return false;
	}
	return true;
}
?>