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
 * Call the functions added to a filter hook.
 *
 * The callback functions attached to filter hook $tag are invoked by calling
 * this function. This function can be used to create a new filter hook by
 * simply calling this function with the name of the new hook specified using
 * the $tag parameter.
 *
 * The function allows for additional arguments to be added and passed to hooks.
 * <code>
 * function example_hook($string, $arg1, $arg2)
 * {
 *		//Do stuff
 *		return $string;
 * }
 * $value = apply_filters('example_filter', 'filter me', 'arg1', 'arg2');
 * </code>
 *
 * @package WordPress
 * @global array $filters Stores all of the filters
 *
 * @param string $filter_name The name of the filter hook.
 * @param mixed $value The value on which the filters hooked to <tt>$filter_name</tt> are applied on.
 * @param mixed $var,... Additional variables passed to the functions hooked to <tt>$filter_name</tt>.
 * @return mixed The filtered value after all hooked functions are applied to it.
 */
function apply_filters($filter_name, $string=''){
	global $filters;
	$args = func_get_args();

	// Do 'all' actions first
	if ( isset($filters['all']) ) {
		_call_all_hook($args);
	}

	if(!isset($filters[$filter_name])) {
		return $string;
	}

	ksort($filters[$filter_name]);

	reset( $filters[$filter_name] );

	global $current_filter;
	$current_filter = $filter_name;

	do {
		foreach((array) current($filters[$filter_name]) as $filter) {
			$filter_function = $filter['function'];
			$args[1] = $string;
			$string = call_user_func_array($filter['function'], array_slice($args, 1, (int) $filter['num_args']));
		}
	} while ( next($filters[$filter_name]) !== false );

	$current_filter = '';

	return $string;
}

/**
 * Execute functions hooked on a specific action hook.
 *
 * This function invokes all functions attached to action hook $action_name.
 * It is possible to create new action hooks by simply calling this function,
 * specifying the name of the new hook using the <tt>$action_name</tt>
 * parameter.
 *
 * You can pass extra arguments to the hooks, much like you can with
 * apply_filters().
 *
 * @see apply_filters() This function uses apply_filters() but simply discards the result of it.
 */
function do_action($action_name){
	//func_get_args() can't be used as a function parameter
	$args = func_get_args();
	call_user_func_array('apply_filters', $args);
}

/**
 * Execute functions hooked on a specific action hook, specifying arguments in an array.
 *
 * @see do_action() This function is identical, but the arguments passed to the
 * functions hooked to <tt>$tag</tt> are supplied using an array.
 *
 * @package WordPress
 * @subpackage Plugin
 * @since 2.1
 * @global array $wp_filter Stores all of the filters
 * @global array $wp_actions Increments the amount of times action was triggered.
 *
 * @param string $tag The name of the action to be executed.
 * @param array $args The arguments supplied to the functions hooked to <tt>$tag</tt>
 * @return null Will return null if $tag does not exist in $wp_filter array
 */
function do_action_ref_array($tag, $args) {
	global $filters;

	global $current_filter;
	$current_filter = $tag;

	// Do 'all' actions first
	if ( isset($filters['all']) ) {
		$all_args = func_get_args();
		_call_all_hook($all_args);
	}

	if ( !isset($filters[$tag]) ) {
		return;
	}

	ksort($filters[$tag]);

	reset($filters[$tag]);

	do {
		foreach( (array) current($filters[$tag]) as $action )
			call_user_func_array($action['function'], array_slice($args, 0, (int) $action['num_args']));

	} while ( next($filters[$tag]) !== false );

	$current_filter = '';
}

/**
 * Hooks a function or method to a specific filter action.
 *
 * Filters are the hooks that Lilina launches to modify text of various types
 * before adding it to the database or sending it to the browser screen. Plugins
 * can specify that one or more of its PHP functions is executed to
 * modify specific types of text at these times, using the Filter API.
 *
 * To use the API, the following code should be used to bind a callback to the
 * filter.
 *
 * <code>
 * function example_hook($example) { echo $example; }
 * add_filter('example_filter', 'example_hook');
 * </code>
 *
 * In WordPress 1.5.1+, hooked functions can take extra arguments that are set
 * when the matching do_action() or apply_filters() call is run. The
 * $num_args allow for calling functions only when the number of args
 * match. Hooked functions can take extra arguments that are set when the
 * matching do_action() or apply_filters() call is run. For example, the action
 * comment_id_not_found will pass any functions that hook onto it the ID of the
 * requested comment.
 *
 * <strong>Note:</strong> the function will return true no matter if the
 * function was hooked fails or not. There are no checks for whether the
 * function exists beforehand and no checks to whether the <tt>$function_to_add
 * is even a string. It is up to you to take care and this is done for
 * optimization purposes, so everything is as quick as possible.
 *
 * @author WordPress
 * @global array $filters Stores all of the filters added in the form of
 *	filters['tag']['array of priorities']['array of functions serialized']['array of ['array (function, num_args)']']
 *
 * @param string $filter The name of the filter to hook the $function_to_add to.
 * @param callback $function The name of the function to be called when the filter is applied.
 * @param int $priority optional. Used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
 * @param int $num_args optional. The number of arguments the function accept (default 1).
 * @return boolean true
 */
function add_filter($filter, $function, $priority = 10, $num_args=1) {
	global $filters;

	$id = _build_callback_string($function);

	$filters[$filter][$priority][$id] = array(
		'function'	=> $function,
		'num_args'	=> $num_args,
	);

	return true;
}

/**
 * Hooks a function on to a specific action.
 *
 * Actions are the hooks that the Lilina core launches at specific points
 * during execution, or when specific events occur. Plugins can specify that
 * one or more of its PHP functions are executed at these points, using the
 * Action API.
 *
 * @uses add_filter() Adds an action. Parameter list and functionality are the same.
 *
 * @author WordPress
 *
 * @param string $action The name of the action to which the $function_to_add is hooked.
 * @param callback $function The name of the function you wish to be called.
 * @param int $priority optional. Used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
 * @param int $num_args optional. The number of arguments the function accept (default 1).
 */
function add_action($action, $function, $priority = 10, $num_args=0) {
	add_filter($action, $function, $priority, $num_args);
}

/**
 * Check if any filter has been registered for a hook.
 *
 * @global array $filters Stores all of the filters
 *
 * @param string $filter
 * @return boolean
 */
function has_filter($filter) {
	global $filters;
	return !empty($filters[$filter]);
}

/**
 * Check if any action has been registered for a hook.
 *
 * @see has_filter() has_action() is an alias of has_filter().
 *
 * @param string $action
 * @return boolean
 */
function has_action($action) {
	return has_filter($action);
}

/**
 * Removes a function from a specified filter hook.
 *
 * This function removes a function attached to a specified filter hook. This
 * method can be used to remove default functions attached to a specific filter
 * hook and possibly replace them with a substitute.
 *
 * To remove a hook, the $function_to_remove and $priority arguments must match
 * when the hook was added. This goes for both filters and actions. No warning
 * will be given on removal failure.
 *
 * @author WordPress
 * @global array $filters Stores all of the filters
 *
 * @param string $filter The filter hook to which the function to be removed is hooked.
 * @param callback $function_to_remove The name of the function which should be removed.
 * @param int $priority optional. The priority of the function (default: 10).
 * @return boolean Whether the function existed before it was removed.
 */
function remove_filter($filter, $function_to_remove, $priority = 10) {
	$function_to_remove = _build_callback_string($function_to_remove);

	$r = isset($GLOBALS['filters'][$filter][$priority][$function_to_remove]);

	if ( true === $r) {
		unset($GLOBALS['filters'][$filter][$priority][$function_to_remove]);
		if ( empty($GLOBALS['filters'][$filter][$priority]) )
			unset($GLOBALS['filters'][$filter][$priority]);
	}

	return $r;
}

/**
 * Removes a function from a specified action hook.
 *
 * @see remove_filter
 *
 * @param string $filter The filter hook to which the function to be removed is hooked.
 * @param callback $function_to_remove The name of the function which should be removed.
 * @param int $priority optional. The priority of the function (default: 10).
 * @return boolean Whether the function existed before it was removed.
 */
function remove_action($filter, $function_to_remove, $priority = 10) {
	return remove_filter($filter, $function_to_remove, $priority);
}

/**
 * Calls the 'all' hook, which will process the functions hooked into it.
 *
 * The 'all' hook passes all of the arguments or parameters that were used for
 * the hook, which this function was called for.
 *
 * This function is used internally for apply_filters(), do_action(), and
 * do_action_ref_array() and is not meant to be used from outside those
 * functions. This function does not check for the existence of the all hook, so
 * it will fail unless the all hook exists prior to this function call.
 *
 * @author WordPress
 * @access private
 *
 * @uses $filters Used to process all of the functions in the 'all' hook
 *
 * @param array $args The collected parameters from the hook that was called.
 * @param string $hook Optional. The hook name that was used to call the 'all' hook.
 */
function _call_all_hook($args) {
	global $filters;

	reset( $filters['all'] );
	do {
		foreach( (array) current($filters['all']) as $the_ )
			if ( !is_null($the_['function']) )
				call_user_func_array($the_['function'], $args);

	} while ( next($filters['all']) !== false );
}

/**
 * Build a unique ID to use for identification of a callback
 *
 * @param callback $callback Anything valid as a callback, as per PHP's pseudo-type
 * @return string Unique ID
 */
function _build_callback_string($callback) {
	if(is_string($callback))
		return $callback;
	elseif(is_array($callback) && is_string($callback[0]))
		return $callback[0] . $callback[1];
	elseif(is_array($callback) && is_object($callback[0]))
		return get_class($callback[0]).$callback[1];
	elseif(is_object($callback) && function_exists('spl_object_hash'))
		return spl_object_hash($callback);

	throw new Exception('Invalid callback: ' . $callback);
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
	$headers = array(
		'name' => 'Plugin Name',
		'uri' => 'Plugin URI',
		'description' => 'Description',
		'author' => 'Author',
		'author_uri' => 'Author URI',
		'version' => 'Version',
		'min_version' => 'Min Version',
		'id' => 'ID',
	);
	$headers = apply_filters('plugin_headers', $headers);

	foreach ( $headers as $friendly => $regex ) {
		$value = null;

		$success = preg_match('|' . preg_quote($regex, '|') . ':(.*)|i', $plugin_data, $value);
		if (!$success) {
			$value = array(1 => '');
		}

		$value = trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $value[1]));
		$vals[$friendly] = $value;
	}

	// New-style docblock parsing
	$docblock = parse_docblock($plugin_data);
	// Use the short and long description as the name and description respectively
	if (!empty($docblock['short'])) {
		$vals['name'] = $docblock['short'];
	}
	if (!empty($docblock['long'])) {
		$vals['description'] = $docblock['long'];
	}

	// Parse the docblock tags
	foreach ($docblock['tags'] as $name => $value) {
		switch ($name) {
			case 'author':
				$value = explode('<', $value);
				$vals['author'] = trim($value[0]);
				if (!empty($value[1])) {
					$vals['author_uri'] = trim($value[1], ' <>');
				}
				break;
			case 'version':
				$vals['version'] = $value;
				break;
			case 'link':
				$vals['uri'] = $value;
				break;
			case 'id':
				$vals['id'] = $value;
				break;
			case 'requires':
				$vals['min_version'] = $value;
				break;
		}
	}

	if (empty($vals['version']))
		$vals['version'] = '1.0';

	if (empty($vals['min_version']))
		$vals['min_version'] = LILINA_CORE_VERSION;
	
	if (empty($vals['id']))
		$vals['id'] = 'unknown';

	$plugin = (object) $vals;
	return $plugin;
}

/**
 * Parse a DocBlock into tags, short description and long description
 *
 * @param string $contents Contents of a PHP file
 * @return array
 */
function parse_docblock($contents) {
	$docblock = get_file_docblock($contents);
	$docblock = preg_replace('#[ \t]*(?:\/\*\*|\*\/|\*)?[ ]{0,1}(.*)?#', '$1', $docblock);
	$docblock = explode("\n", ltrim($docblock, "\r\n"));

	$tags = array();
	$short = '';
	$long = '';
	$done_short = false;
	$line_number = 0;

	foreach ($docblock as $line) {
		$line_number++;

		if (strpos($line, "@") === 0) {
			list($key, $value) = explode(" ", $line, 2);
			$key = substr($key, 1);
			$tags[$key] = trim($value);
		}
		else {
			if (empty($line) && !$done_short) {
				$done_short = true;
			}
			if (!$done_short && $line_number < 3) {
				$short .= $line;
			}
			else {
				$long .= $line;
			}
		}
	}
	return array('tags' => $tags, 'short' => $short, 'long' => $long);
}

function get_file_docblock($contents) {
	$tokens = token_get_all($contents);
	foreach ($tokens as $token) {
		if (($token[0] == T_OPEN_TAG) || ($token[0] == T_WHITESPACE)) {
			continue;
		} elseif ($token[0] == T_DOC_COMMENT) {
			return $token[1];
		} else {
			return '';
		}
	}
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
 * Load plugins
 *
 * Loads in the activated plugins data then loads the plugins.
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
		if ('' !== $plugin && file_exists(LILINA_CONTENT_DIR . '/plugins/' . $plugin))
			include_once(LILINA_CONTENT_DIR . '/plugins/' . $plugin);
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
 * @return bool True if file exists and is valid, otherwise an exception will be thrown
 */
function validate_plugin($filename) {
	switch(validate_file($filename)) {
		case 1:
		case 2:
			throw new Exception(_r('Invalid plugin path.'), Errors::get_code('admin.plugins.invalid_path'));
			break;

		default:
			if(file_exists(get_plugin_dir() . $filename))
				return true;
			else
				throw new Exception(_r('Plugin file was not found.'), Errors::get_code('admin.plugins.not_found'));
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
	return LILINA_CONTENT_DIR . '/plugins/';
}

lilina_plugins_init();
require_once(LILINA_INCPATH . '/core/default-actions.php');
?>