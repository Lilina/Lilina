<?php
/**
 * Plugin management
 * @package Lilina
 * @subpackage Plugins
 */

/**
 * Plugin management
 * @package Lilina
 * @subpackage Plugins
 */

class Lilina_Plugins {
	/**
	 * Plugin directory
	 * @var string
	 */
	protected static $directory = '';

	/**
	 * All registered filters
	 * @var array
	 */
	protected static $filters = array();

	/**
	 * Currently executing filter
	 * @var string
	 */
	protected static $current_filter = '';

	/**
	 * Activated plugins
	 *
	 * Array of meta objects
	 * @var array
	 */
	protected static $activated = array();

	/**
	 * Activated plugin files
	 *
	 * Array of paths, indexed by ID
	 * @var array
	 */
	protected static $activated_files = array();

	/**
	 * Available plugins
	 *
	 * Cached result of `Lilina_Plugins::get_available()`
	 * @var array
	 */
	protected static $available = array();

	/**
	 * Available plugin files
	 *
	 * Cached result of `Lilina_Plugins::get_available_files()`
	 * @var array
	 */
	protected static $available_files = array();

	/**
	 * Protect class from being instantiated
	 */
	protected function __construct() {}

	/**
	 * Initialize the plugin system
	 *
	 * Loads in the activated plugins data then loads the plugins.
	 */
	public static function init() {
		self::$directory = get_plugin_dir();
		self::$activated_files = self::load();

		require_once(LILINA_INCPATH . '/core/default-actions.php');

		foreach (self::$activated_files as $file) {
			if (!file_exists(self::$directory . $file)) {
				continue;
			}

			$info = self::get_meta(self::$directory . $file);
			if ($info === false) {
				continue;
			}
			self::$activated[$info->id] = $info;

			include_once(self::$directory . $file);
		}
	}

	/**
	 * Load activated plugins
	 *
	 * @return array
	 */
	protected static function load() {
		return get_option('activated_plugins', array());
	}

	/**
	 * Save activated plugins
	 *
	 * @param array $plugins Either an array of filenames or an array of meta objects
	 * @return boolean
	 */
	protected static function save() {
		return update_option('activated_plugins', self::$activated_files);
	}

	/**
	 * Get the meta information for a plugin
	 *
	 * @param string $id
	 * @return array|null
	 */
	public static function get($id) {
		if (!empty(self::$activated[$id])) {
			return self::$activated[$id];
		}

		$all = self::get_available();
		if (!empty($all[$id])) {
			return $all[$id];
		}

		return null;
	}

	/**
	 * Call the functions added to a filter hook.
	 *
	 * The callback functions attached to filter hook $tag are invoked by calling
	 * this function. This function can be used to create a new filter hook by
	 * simply calling this function with the name of the new hook specified using
	 * the `$filter_name` parameter.
	 *
	 * The function allows for additional arguments to be added and passed to hooks.
	 * <code>
	 * function example_hook($string, $arg1, $arg2) {
	 *		//Do stuff
	 *		return $string;
	 * }
	 * $value = Lilina_Plugins::filter('example_filter', 'filter me', 'arg1', 'arg2');
	 * </code>
	 *
	 * @param string $filter_name The name of the filter hook.
	 * @param mixed $value The value on which the filters hooked to `$filter_name` are applied on.
	 * @param mixed $var,... Additional variables passed to the functions hooked to `$filter_name`.
	 * @return mixed The filtered value after all hooked functions are applied to it.
	 */
	public static function filter($filter_name, $string = ''){
		$args = func_get_args();

		// Do 'all' actions first
		self::all_hook($args);

		if (!isset(self::$filters[$filter_name])) {
			return $string;
		}

		ksort(self::$filters[$filter_name]);

		reset(self::$filters[$filter_name]);

		self::$current_filter = $filter_name;

		do {
			foreach((array) current(self::$filters[$filter_name]) as $filter) {
				$filter_function = $filter['function'];
				$args[1] = $string;
				$string = call_user_func_array($filter['function'], array_slice($args, 1, (int) $filter['num_args']));
			}
		} while ( next(self::$filters[$filter_name]) !== false );

		self::$current_filter = '';

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
	 * @see filter() This function uses filter() but simply discards the result of it.
	 * @param string $action The name of the hook
	 * @param mixed $var,... Additional variables passed to the functions hooked to `$action`
	 */
	public static function act($action){
		$args = func_get_args();
		call_user_func_array(array(get_class(), 'filter'), $args);
	}

	/**
	 * Execute functions hooked on a specific action hook, specifying arguments in an array.
	 *
	 * @param string $tag The name of the action to be executed.
	 * @param array $args The arguments supplied to the functions hooked to `$tag`
	 * @return mixed Will return null if $tag does not exist in $wp_filter array
	 */
	public static function filter_reference($tag, $args) {
		self::$current_filter = $tag;

		// Do 'all' actions first
		$all_args = func_get_args();
		self::all_hook($all_args);

		if (!isset(self::$filters[$tag])) {
			return;
		}

		ksort(self::$filters[$tag]);

		reset(self::$filters[$tag]);

		do {
			foreach ((array) current(self::$filters[$tag]) as $action) {
				call_user_func_array($action['function'], array_slice($args, 0, (int) $action['num_args']));
			}

		} while ( next(self::$filters[$tag]) !== false );

		self::$current_filter = '';
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
	 * Lilina_Plugins::register('example_filter', 'example_hook');
	 * </code>
	 *
	 * <strong>Note:</strong> the function will return true no matter if the
	 * function was hooked fails or not. There are no checks for whether the
	 * function exists beforehand and no checks to whether the `$function_to_add`
	 * is even a string. It is up to you to take care and this is done for
	 * optimization purposes, so everything is as quick as possible.
	 *
	 * @param string $filter The name of the filter to hook the $function_to_add to.
	 * @param callback $function The name of the function to be called when the filter is applied.
	 * @param int $priority optional. Used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
	 * @param int $num_args optional. The number of arguments the function accept (default 1).
	 * @return boolean
	 */
	public static function register($filter, $function, $priority = 10, $num_args=1) {
		$id = self::build_callback_string($function);

		self::$filters[$filter][$priority][$id] = array(
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
	 * @uses Lilina_Plugins::register() Adds an action. Parameter list and functionality are the same.
	 *
	 * @param string $action The name of the action to which the $function_to_add is hooked.
	 * @param callback $function The name of the function you wish to be called.
	 * @param int $priority optional. Used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
	 * @param int $num_args optional. The number of arguments the function accept (default 1).
	 */
	public static function register_action($action, $function, $priority = 10, $num_args=0) {
		add_filter($action, $function, $priority, $num_args);
	}

	/**
	 * Check if any filter has been registered for a hook.
	 *
	 * @param string $filter
	 * @return boolean
	 */
	public static function is_registered($filter) {
		return !empty(self::$filters[$filter]);
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
	 * @param string $filter The filter hook to which the function to be removed is hooked.
	 * @param callback $function_to_remove The name of the function which should be removed.
	 * @param int $priority optional. The priority of the function (default: 10).
	 * @return boolean Whether the function existed before it was removed.
	 */
	public static function unregister($filter, $function_to_remove, $priority = 10) {
		$function_to_remove = self::build_callback_string($function_to_remove);

		$r = isset(self::$filters[$filter][$priority][$function_to_remove]);

		if ($r !== true) {
			return $r;
		}

		unset(self::$filters[$filter][$priority][$function_to_remove]);
		if ( empty(self::$filters[$filter][$priority]) ) {
			unset(self::$filters[$filter][$priority]);
		}

		return true;
	}

	/**
	 * Calls the 'all' hook, which will process the functions hooked into it.
	 *
	 * The 'all' hook passes all of the arguments or parameters that were used for
	 * the hook, which this function was called for.
	 *
	 * This function is used internally for filter(), act(), and do_reference()
	 * and is not meant to be used from outside those functions. This function
	 * does not check for the existence of the all hook, so it will fail unless
	 * the all hook exists prior to this function call.
	 *
	 * @param array $args The collected parameters from the hook that was called.
	 * @param string $hook Optional. The hook name that was used to call the 'all' hook.
	 */
	protected static function all_hook($args) {
		if (!isset(self::$filters['all'])) {
			return false;
		}
	
		reset(self::$filters['all']);
		do {
			foreach ((array) current(self::$filters['all']) as $the_) {
				if (!is_null($the_['function'])) {
					call_user_func_array($the_['function'], $args);
				}
			}

		} while (next(self::$filters['all']) !== false);
	}

	/**
	 * Build a unique ID to use for identification of a callback
	 *
	 * @param callback $callback Anything valid as a callback, as per PHP's pseudo-type
	 * @return string Unique ID
	 */
	protected static function build_callback_string($callback) {
		if (is_string($callback)) {
			return $callback;
		}
		elseif (is_array($callback) && is_string($callback[0])) {
			return $callback[0] . $callback[1];
		}
		elseif (is_array($callback) && is_object($callback[0])) {
			return get_class($callback[0]) . $callback[1];
		}
		elseif (is_object($callback) && function_exists('spl_object_hash')) {
			return spl_object_hash($callback);
		}

		throw new Exception('Invalid callback: ' . $callback);
	}

	/**
	 * Gets metadata about plugins
	 *
	 * @param string $plugin_file Plugin file to search for metadata
	 * @return object Plugin metadata
	 */
	protected static function get_meta($plugin_file) {
		$vals = array();
		$vals['filename'] = $plugin_file;

		$plugin_data = file_get_contents($plugin_file);
		$headers = array(
			'name' => 'Plugin Name',
			'uri' => 'Plugin URI',
			'description' => 'Description',
			'author' => 'Author',
			'author_uri' => 'Author URI',
			'version' => 'Version',
			'min_version' => 'Min Version'
		);
		$headers = apply_filters('plugin_headers', $headers);

		foreach ($headers as $friendly => $regex) {
			$value = null;

			$success = preg_match('|' . preg_quote($regex, '|') . ':(.*)|i', $plugin_data, $value);
			if (!$success) {
				$value = array(1 => '');
			}

			$value = trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $value[1]));
			$vals[$friendly] = $value;
		}

		// New-style docblock parsing
		$docblock = self::parse_docblock($plugin_data);
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
		
		// Only create an ID for plugins not using docblocks that also have a name.
		// This avoids accidentally adding random files
		if (empty($vals['id']) && !empty($vals['name']) && empty($docblock['short'])) {
			$vals['id'] = sha1($plugin_file);
		}
		elseif (empty($vals['id'])) {
			return false;
		}

		$plugin = (object) $vals;
		return $plugin;
	}

	/**
	 * Parse a DocBlock into tags, short description and long description
	 *
	 * @param string $contents Contents of a PHP file
	 * @return array
	 */
	protected static function parse_docblock($contents) {
		$docblock = self::get_file_docblock($contents);
		$docblock = preg_replace('#[ \t]*(?:\/\*\*|\*\/|\*)?[ ]{0,1}(.*)?#', '$1', $docblock);
		$docblock = explode("\n", ltrim($docblock, "\r\n"));

		$tags = array();
		$short = '';
		$long = '';
		$done_short = false;
		$line_number = 0;

		foreach ($docblock as $line) {
			$line_number++;

			if (strpos($line, "@") === 0 && strpos($line, ' ') !== false) {
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

	/**
	 * Get the file-level DocBlock from a string
	 *
	 * @param string $contents PHP source to tokenize
	 * @return string File-level DocBlock
	 */
	protected static function get_file_docblock($contents) {
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
	 * Get paths of files which may be plugins
	 *
	 * Note: this does not do any validation to ensure that the file is
	 * actually a plugin file.
	 * @return array Available PHP files (may not actually be plugins)
	 */
	public static function get_available_files() {
		if (!empty(self::$available_files)) {
			return self::$available_files;
		}
		$available = array();
		$iterator = new RecursiveDirectoryIterator(self::$directory, FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_SELF | FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS);
		foreach ($iterator as $path => $file) {
			if ($file->hasChildren()) {
				$children = $file->getChildren();
				foreach ($children as $path => $file) {
					if ($file->getExtension() !== 'php') {
						continue;
					}
					$available[] = $path;
				}
			}
			else {
				if ($file->getExtension() !== 'php') {
					continue;
				}

				$available[] = $path;
			}
		}

		self::$available_files = $available;
		return self::$available_files;
	}

	/**
	 * Get available plugins
	 *
	 * @return array Available plugins (array of meta objects)
	 */
	public static function get_available() {
		if (!empty(self::$available)) {
			return self::$available;
		}

		$files = self::get_available_files();
		$available = array();
		foreach ($files as $file) {
			$meta = self::get_meta($file);
			if ($meta === false) {
				continue;
			}
			$available[$meta->id] = $meta;
		}

		self::$available = $available;
		return self::$available;
	}

	/**
	 * Activate a plugin
	 *
	 * @since 1.0
	 *
	 * @param string $id Plugin ID
	 * @return bool Whether plugin was activated
	 */
	public static function activate($id) {
		$available = self::get_available();
		if (empty($available[$id])) {
			return false;
		}
		$meta = $available[$id];

		// Normalise the paths so that str_replace works
		$base = str_replace('\\', '/', self::$directory);
		$file = str_replace('\\', '/', $meta->filename);

		self::$activated_files[$meta->id] = str_replace($base, '', $file);
		self::$activated[$meta->id] = $meta;
		return self::save();
	}

	/**
	 * Get the activated plugins
	 *
	 * @return array
	 */
	public static function get_activated() {
		return self::$activated;
	}

	/**
	 * Check whether a plugin is currently activated
	 *
	 * @param string $id Plugin ID
	 * @return bool
	 */
	public static function is_activated($id) {
		return !empty(self::$activated_files[$id]);
	}

	/**
	 * Deactivate a plugin
	 *
	 * @since 1.0
	 *
	 * @param string $id Plugin ID
	 * @return bool Whether plugin was deactivated
	 */
	public static function deactivate($id) {
		if (!isset(self::$activated_files[$id]))
			return false;

		unset(self::$activated_files[$id]);
		unset(self::$activated[$id]);
		return self::save();
	}
}

/**
 * Apply a filter
 *
 * @see Lilina_Plugins::filter
 */
function apply_filters() {
	$args = func_get_args();
	return call_user_func_array(array('Lilina_Plugins', 'filter'), $args);
}

/**
 * Do an action
 *
 * @see Lilina_Plugins::act
 */
function do_action() {
	$args = func_get_args();
	call_user_func_array(array('Lilina_Plugins', 'filter'), $args);
}

/**
 * Register a filter
 *
 * @see Lilina_Plugins::register
 */
function add_filter() {
	$args = func_get_args();
	call_user_func_array(array('Lilina_Plugins', 'register'), $args);
}

/**
 * Register an action
 *
 * @see Lilina_Plugins::register_action
 */
function add_action() {
	$args = func_get_args();
	call_user_func_array(array('Lilina_Plugins', 'register_action'), $args);
}

/**
 * Unregister a filter
 *
 * @see Lilina_Plugins::unregister
 */
function remove_filter() {
	$args = func_get_args();
	call_user_func_array(array('Lilina_Plugins', 'unregister'), $args);
}

/**
 * Unregister an action
 *
 * @see Lilina_Plugins::unregister
 */
function remove_action() {
	$args = func_get_args();
	call_user_func_array(array('Lilina_Plugins', 'unregister'), $args);
}

/**
 * Check if a filter has any callbacks registered
 *
 * @deprecated
 * @see Lilina_Plugins::is_registered
 */
function has_filter($name) {
	return Lilina_Plugins::is_registered($name);
}

/**
 * Check if an action has any callbacks registered
 *
 * @deprecated
 * @see Lilina_Plugins::is_registered
 */
function has_action() {
	return Lilina_Plugins::is_registered($name);
}

/**
 * Execute functions hooked on a specific action hook, specifying arguments in an array.
 *
 * @deprecated
 * @see Lilina_Plugins::filter_reference
 */
function do_action_ref_array($tag, $args) {
	return Lilina_Plugins::filter_reference($tag, $args);
}