<?php
/**
 * Default configuration
 *
 * Default settings stored in the global $settings variable
 * DO NOT MAKE CHANGES IN THIS FILE, AS THEY WILL BE OVERRIDDEN WHEN YOU UPDATE
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

defined('LILINA_PATH') or die('Restricted access');
/** Ensures that we have this for maybe_unserialize() */
require_once(LILINA_INCPATH . '/core/misc-functions.php');

/**
 * Make sure that we don't load the settings multiple times
 */
if(!defined('LOADED_SETTINGS')) {
	/**
	 * Holds all settings for Lilina
	 *
	 * Overwritten with values from conf/settings.php
	 * @global array $settings
	 */
	global $settings, $default_settings;
	$settings							= array();

	$settings['baseurl']				= 'http://localhost/';
	$settings['auth']					= array('user' => 'username', 'pass' => 'password');
	//Just in case we need to check against them
	$default_settings = $settings;

	/**
	 * Holds all the users settings
	 *
	 * Holds the new $settings variables which overwrites
	 * all our old settings here.
	 */
	require_once(LILINA_PATH . '/content/system/config/settings.php') ;

	/**
	 * Unserialize any settings which were serialized, e.g. objects
	 */
	foreach($settings as $key => $the_setting) {
		$settings[$key] = maybe_unserialize($the_setting);
	}
	
	/**
	 * Stores the location of the language directory. First looks for language folder in content
	 * and uses that folder if it exists. Or it uses the "languages" folder in LILINA_INCPATH.
	 *
	 * @since 1.0
	 */
	if ( !defined('LANGDIR'))
		define('LANGDIR', '/content/locales');

	//Settings that use other settings variables

	if(!defined('LILINA_CONTENT_DIR'))
		define('LILINA_CONTENT_DIR', LILINA_PATH . '/content');

	if(!defined('LILINA_CACHE_DIR'))
		define('LILINA_CACHE_DIR', LILINA_CONTENT_DIR . '/system/cache/');

	if(!defined('LILINA_DATA_DIR'))
		define('LILINA_DATA_DIR', LILINA_CONTENT_DIR . '/system/data/');
}


/**
 * Attempt to load the class before PHP fails with an error.
 *
 * This method is called automatically in case you are trying to use a class which hasn't been defined yet.
 * @param string $class_name Class called by the user
 */
function __autoload($class_name) {
	if (strpos($class_name, '_') !== false) {
		$file = str_replace('_', '/', $class_name);
		$file = LILINA_INCPATH . '/core/' . $file . '.php';
		if (file_exists($file)) {
			require_once($file);
		}
		return;
	}

	$class_file = strtolower($class_name) . '.php';
	if(file_exists(LILINA_INCPATH . '/core/class-' . $class_file)) {
		require_once(LILINA_INCPATH . '/core/class-' . $class_file);
	}
}

spl_autoload_register('__autoload');
Options::load();
?>