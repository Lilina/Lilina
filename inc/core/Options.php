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
	protected static $lazy = array();

	/**
	 * Load options
	 */
	public static function load() {
		// Ensure it's loaded up
		self::get('baseurl');
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

		$option = Lilina_DB::get_adapter()->retrieve(array(
			'table' => 'options',
			'where' => array(array('key', '===', $option)),
			'limit' => 1,
			'reindex' => 'key'
		));

		if (empty($option)) {
			// Backwards compatibility, this gets upgraded out
			if ($option === 'baseurl' && !empty($settings['baseurl'])) {
				return $settings['baseurl'];
			}
			return $default;
		}

		$option = array_shift($option);

		return maybe_unserialize($option['value']);
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
		if ($option_name === 'auth' || $option_name === 'files')
			return false;

		$option = apply_filters("update_option-$option_name", $new_value);
		$previous = self::get($option_name);

		if ($previous === null) {
			Lilina_DB::get_adapter()->insert(array('key' => $option_name, 'value' => $new_value), array(
				'table' => 'options',
				'primary' => 'key'
			));
		}
		else {
			Lilina_DB::get_adapter()->update(array('value' => $new_value), array(
				'table' => 'options',
				'where' => array(
					array('key', '==', $option_name)
				),
				'limit' => 1
			));
		}
	}

	/**
	 * Update the value of an option, but do not save it
	 *
	 * @deprecated This was only an implementation detail, and has been removed
	 * @see Options::update()
	 * @param string $option Option key to change
	 * @param mixed $new_value New value of <tt>$option</tt>
	 */
	public static function lazy_update($option_name, $new_value) {
		self::$lazy[$option_name] = $new_value;
		return self::update($option_name, $new_value);
	}

	/**
	 * Save options to database
	 */
	public static function save() {
		foreach (self::$lazy as $key => $value) {
			self::update($key, $value);
		}
		return true;
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