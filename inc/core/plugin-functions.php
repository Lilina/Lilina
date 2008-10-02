<?php
/**
 * Functions for plugins
 *
 * Everything that handles plugins. Loads, adds, removes,
 * recalculates lists, etc
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

defined('LILINA_PATH') or die('Restricted access');


/**
 * Applies filters specified by <tt>$filter_name</tt> on <tt>$string</tt>
 *
 * Thanks to WordPress for inspiration
 * @todo Document
 * @uses get_hooked Get the hooked plugins at the specifed plugin
 * @param string $filter_name Hook to call plugin functions for
 * @param string $string String to run through filters
 */
function apply_filters($filter_name, $string=''){
	global $filters;
	if(!isset($filters[$filter_name])) {
		return $string;
	}
	$args = func_get_args();

	ksort($filters[$filter_name]);

	reset( $filters[$filter_name] );

	global $current_filter;
	$current_filter = $filter_name;

	do {
		foreach((array) current($filters[$filter_name]) as $filter) {
			$filter_function = $filter['function'];
			$string = call_user_func_array($filter['function'], array_slice($args, 1, (int) $filter['num_args']));
		}
	} while ( next($filters[$filter_name]) !== false );

	$current_filter = '';

	return $string;
}

/**
 * Applies filters specified by <tt>$filter_name</tt> on <tt>$string</tt>
 *
 * Thanks to WordPress for inspiration
 * @todo Document
 * @uses get_hooked Get the hooked plugins at the specifed plugin
 * @param string $action_name Hook to call plugin functions for
 */
function do_action($action_name){
	//func_get_args() can't be used as a function parameter
	$args = func_get_args();
	call_user_func_array('apply_filters', $args);
}

/**
* Register plugin function with system
*
* Adds plugin function to $hooked_plugins under the specified hook
* @deprecated Use add_filter instead
* @param string $function Plugin function to register
* @param string $hook Hook to register function under
*/
function register_filter($filter, $function, $num_args=1) {
	add_filter($filter, $function, 0, $num_args);
}

/**
* Register plugin action with system
*
* Adds plugin function to $hooked_plugins under the specified hook
* @deprecated Use add_action() instead
* @param string $function Plugin function to register
* @param string $function Hook to register function under
*/
function register_action($action, $function, $num_args=0) {
	add_filter($action, $function, $num_args);
}

/**
* Register plugin function with system
*
* Adds plugin function to $hooked_plugins under the specified hook
*
* @param string $function Plugin function to register
* @param string $hook Hook to register function under
*/
function add_filter($filter, $function, $priority = 0, $num_args=1) {
	global $filters;
	$filters[$filter][$priority][$function]	= array(
		'function'	=> $function,
		'num_args'	=> $num_args,
		);
}

/**
* Register plugin action with system
*
* Adds plugin function to $hooked_plugins under the specified hook
*
* @param string $function Plugin function to register
* @param string $function Hook to register function under
*/
function add_action($action, $function, $priority = 0, $num_args=0) {
	add_filter($action, $function, $priority, $num_args);
}

/**
* Get plugins and load them
*
* Gets all activated plugins and require_once()s their files
*/
function get_plugins() {
	global $activated_plugins, $registered_plugins;
	foreach($activated_plugins as $plugin_name) {
		require_once(LILINA_INCPATH . '/plugins/' . $registered_plugins[$plugin_name]['file']);
	}
}

/**
 * Gets metadata about plugins
 *
 * Thanks to Wordpress, admin-functions.php, lines 1525-1534
 * @author Wordpress Development Team
 * @param string $plugin_file Plugin file to search for metadata
 * @return object Plugin metadata
 */
function plugins_meta($plugin_file) {
	$plugin_data = implode('', file($plugin_file));
	preg_match("|Plugin Name:(.*)|i", $plugin_data, $plugin_name);
	preg_match("|Plugin URI:(.*)|i", $plugin_data, $plugin_uri);
	preg_match("|Description:(.*)|i", $plugin_data, $description);
	preg_match("|Author:(.*)|i", $plugin_data, $author_name);
	preg_match("|Author URI:(.*)|i", $plugin_data, $author_uri);
	//If the plugin sets the version...
	if (preg_match("|Version:(.*)|i", $plugin_data, $version)) {
		//...Let it
		$version = trim($version[1]);
	}
	else {
		//...Otherwise assume it's 1.0
		$version = 1.0;
	}
	//If the plugin sets the version...
	if (preg_match("|Min Version:(.*)|i", $plugin_data, $min_version)) {
		//...Let it
		$min_version = trim($min_version[1]); //F1
	}
	else {
		//...Otherwise assume it's the current version of Lilina
		$min_version = 1.0;
	}
	//Set the $plugin object for returning
	$plugin = new stdClass;
	$plugin->name = $plugin_name[1];
	$plugin->uri = $plugin_uri[1];
	$plugin->description = $description[1];
	$plugin->author = $author_name[1];
	$plugin->author_uri = $author_uri[1];
	$plugin->version = $version;
	$plugin->min_version = $min_version;
	return $plugin;
}

/**
 * Returns available plugin file
 *
 * Gets a list of all PHP files within a given directory. Primarily used for plugins, but
 * can be used for other things, such as _by_ plugins. Probably needs to be renamed to
 * lilina_file_list() and also accept a file type parameter
 *
 * @param string $directory Directory to search for plugin files in
 */
function lilina_plugins_list($directory){
	//Make sure we open it correctly
	if ($handle = opendir($directory)) {
		//Go through all entries
		while (false !== ($file = readdir($handle))) {
			// just skip the reference to current and parent directory
			if ($file != '.' && $file != '..') {
				if (is_dir($directory . '/' . $file)) {
					//Found a directory, let's see if a plugin exists in it,
					//with the same name as the directory
					if(file_exists($directory . '/' . $file . '/' . $file . '.php')) {
						$plugin_list[] = $directory . '/' . $file . '/' . $file . '.php';
					}
				} else {
					//Only add plugin files
					if(strpos($file,'.php') !== FALSE) {
						$plugin_list[] = $directory . '/' . $file;
					}
				}
			}
		}
		// ALWAYS remember to close what you opened
		closedir($handle);
	}
	return $plugin_list;
}

/**
 *
 */
function lilina_plugins_init() {
	$data = new DataHandler();
	$plugins = $data->load('plugins.data');
	if($plugins === null)
		return;

	$plugins = unserialize($plugins);
	
	if(!is_array($plugins) || empty($plugins))
		return;

	foreach ($plugins as $plugin) {
		if ('' !== $plugin && file_exists(LILINA_INCPATH . '/plugins/' . $plugin))
			include_once(LILINA_INCPATH . '/plugins/' . $plugin);
	}

	global $current_plugins;
	$current_plugins = $plugins;
}

/**
 * Validate a plugin filename
 *
 * Checks that the file exists and {@link validate_file() is valid file}. If
 * it either condition is not met, returns false and adds an error to the
 * {@see MessageHandler} stack.
 *
 * @since 1.0
 *
 * @param $filename Path to plugin
 * @return bool True if file exists and is valid, else false
 */
function validate_plugin($filename) {
	switch(validate_file($filename)) {
		case 1:
		case 2:
			MessageHandler::add_error(_r('Invalid plugin path.'));
			break;

		default:
			if(file_exists(get_plugin_dir() . $plugin))
				return true;
			else
				MessageHandler::add_error(_r('Plugin file was not found.'));
	}

	return false;
}

/**
 * Get the plugin storage directory
 *
 * @since 1.0
 *
 * @return string Path to plugin directory
 */
function get_plugin_dir() {
	return LILINA_INCPATH . '/plugins/';
}

lilina_plugins_init();
require_once(LILINA_INCPATH . '/core/default-actions.php');
?>