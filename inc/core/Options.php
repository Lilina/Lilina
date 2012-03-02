<?php
/**
 * Option handler
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * Option handler
 *
 * @package Lilina
 */
class Options {
	/**
	 * Loaded options
	 * @var array
	 */
	protected static $options;

	/**
	 * @var DataHandler
	 */
	protected static $handler;

	public static function handler() {
		if (is_null(self::$handler)) {
			self::$handler = new DataHandler(LILINA_CONTENT_DIR . '/system/config/');
		}
	
		return self::$handler;
	}

	/**
	 * Load options
	 */
	public static function load() {
		self::$handler = new DataHandler(LILINA_CONTENT_DIR . '/system/config/');

		self::$options = self::handler()->load('options.data');
		if(self::$options !== null)
			self::$options = unserialize(self::$options);
		else
			self::$options = array();
		if(!isset(self::$options['cachedir']))
			self::$options['cachedir'] = LILINA_CACHE_DIR;
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
	 * @param string $option Name of option to retrieve.
	 * @param mixed $default Value to default to if none is found. Alternatively used as a "subkey" option for the hardcoded settings
	 * @return mixed Value set for the option.
	 */
	public static function get($option, $default = null) {
		global $settings;

		/** Hardcoded settings in settings.php */
		if($option === 'auth' || $option === 'files') {
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
		if(!isset(self::$options[$option]))
			return $default;

		return maybe_unserialize(self::$options[$option]);
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
	 * @param string $option Option key to change
	 * @param mixed $new_value New value of <tt>$option</tt>
	 */
	public static function update($option_name, $new_value) {
		self::lazy_update($option_name, $new_value);
		return self::save();
	}

	/**
	 * Update the value of an option, but do not save it
	 *
	 * @see Options::update()
	 * @param string $option Option key to change
	 * @param mixed $new_value New value of <tt>$option</tt>
	 */
	public static function lazy_update($option_name, $new_value) {
		if($option_name === 'auth' || $option_name === 'files')
			return false;

		self::$options[$option_name] = apply_filters("update_option-$option_name", $new_value);
	}

	/**
	 * Save options to database
	 *
	 * Serialize the options and save them using DataHandler
	 */
	public static function save() {
		return self::handler()->save('options.data', serialize(self::$options));
	}
}

/**
 * Convenience function for Options::get()
 *
 * @see Options::get()
 */
function get_option($option, $default = null) {
	return Options::get($option, $default);
}

/**
 * Convenience function for Options::update()
 *
 * @see Options::update()
 */
function update_option($option_name, $new_value) {
	return Options::update($option_name, $new_value);
}

/**
 * Convenience function for Options::save()
 *
 * @see Options::save()
 */
function save_options() {
	return Options::save();
}

/**
 * Get the plugin storage directory
 *
 * @return string Path to plugin directory
 */
function get_plugin_dir() {
	return LILINA_CONTENT_DIR . '/plugins/';
}