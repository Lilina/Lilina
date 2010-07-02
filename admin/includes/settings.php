<?php
/**
 * Settings page functions
 *
 * @package Lilina
 * @subpackage Administration
 */

/**
 * available_locales() - {{@internal Missing Short Description}}}
 *
 * {{@internal Missing Long Description}}}
 */
function available_locales() {
	$locale_list = array_map('basename', glob(LILINA_PATH . LANGDIR . '/*.mo'));
	$locale_list = apply_filters('locale_files', $locale_list);
	$locales = array();

	/** Special case for English */
	$locales[]	= array('name' => 'English',
						'file' => '',
						'realname' => 'en');

	foreach($locale_list as $locale) {
		$locale = basename($locale, '.mo');

		if(file_exists( $locale . '.txt' )) {
			$locale_metadata = file_get_contents(LILINA_PATH . LANGDIR . $locale . '.txt');

			preg_match("|Name:(.*)|i", $locale_metadata, $name);

			$locales[$locale] = array(
				'name' => $name,
				'file' => $locale . '.mo',
				'realname' => $locale
			);
		}

		else {
			$locales[$locale] = array(
				'name' => $locale,
				'file' => $locale . '.mo',
				'realname' => $locale
			);
		}
	}
	return $locales;
}

/**
 * available_templates() - {{@internal Missing Short Description}}}
 *
 * {{@internal Missing Long Description}}}
 */
function available_templates() {
	//Make sure we open it correctly
	if ($handle = opendir(LILINA_CONTENT_DIR . '/templates/')) {
		//Go through all entries
		while (false !== ($dir = readdir($handle))) {
			// just skip the reference to current and parent directory
			if ($dir != '.' && $dir != '..') {
				if (is_dir(LILINA_CONTENT_DIR . '/templates/' . $dir)) {
					if(file_exists(LILINA_CONTENT_DIR . '/templates/' . $dir . '/style.css')) {
						$list[] = $dir;
					}
				} 
			}
		}
		// ALWAYS remember to close what you opened
		closedir($handle);
	}
	foreach($list as $the_template) {
		$temp_data = implode('', file(LILINA_CONTENT_DIR . '/templates/' . $the_template . '/style.css'));
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
 * Activate a plugin
 *
 * @since 1.0
 *
 * @param string $plugin_file Relative path to plugin
 * @return bool Whether plugin was activated
 */
function activate_plugin($plugin_file) {
	global $current_plugins;
	$plugin_file = trim($plugin_file);

	try {
		validate_plugin($plugin_file);
	}
	catch (Exception $e) {
		return false;
	}
	$current_plugins[md5($plugin_file)] = $plugin_file;
	
	$data = new DataHandler();
	$data->save('plugins.data', serialize($current_plugins));
	return true;
}

/**
 * Deactivate a plugin
 *
 * @since 1.0
 *
 * @param string $plugin_file Relative path to plugin
 * @return bool Whether plugin was deactivated
 */
function deactivate_plugin($plugin_file) {
	global $current_plugins;
	
	if(!isset($current_plugins[md5($plugin_file)]))
		return false;

	try {
		validate_plugin($plugin_file);
	}
	catch (Exception $e) {
		return false;
	}

	unset($current_plugins[md5($plugin_file)]);
	
	$data = new DataHandler();
	$data->save('plugins.data', serialize($current_plugins));
	return true;
}

/**
 * Register an option for the whitelist
 *
 * @param string $name Option name
 * @param callback $sanitize_callback Callback to sanitize user input.
 */
function register_option($name, $sanitize_callback = null) {
	AdminOptions::instance()->whitelisted[] = $name;
	if ( $sanitize_callback !== null )
		add_filter('options-sanitize-' . $name, $sanitize_callback);
}

/**
 * Add a section to the options page
 *
 * @see AdminOptions::add_section()
 */
function add_option_section($id, $title, $callback) {
	AdminOptions::instance()->add_section($id, $title, $callback);
}

/**
 * Add a field to an option section
 *
 * @see AdminOptions::add_field()
 */
function add_option_field($id, $title, $callback, $page, $section = 'default', $args = array()) {
	AdminOptions::instance()->add_field($id, $title, $callback, $page, $section, $args);
}

/**
 * Controls handling of options and displaying in the admin
 *
 * @package Lilina
 * @subpackage Administration
 */
class AdminOptions {
	protected static $instance = null;
	public $whitelisted = array();
	public $sections = array();

	public function __construct() {
		$this->whitelisted = array('sitename', 'template', 'locale', 'timezone', 'updateon');
	}

	public static function &instance() {
		if ( empty(AdminOptions::$instance) ) {
			AdminOptions::$instance = new AdminOptions();
		}
		return AdminOptions::$instance;
	}

	/**
	 * Add a section to the options page
	 *
	 * @param string $id String for use in the 'id' attribute of tags.
	 * @param string $title Title of the section.
	 * @param string $callback Function that fills the section with the desired content. The function should echo its output.
	 */
	public function add_section($id, $title, $callback) {
		if ( !isset($this->sections[$id]) )
			$this->sections[$id] = array();

		$this->sections[$id] = array('id' => $id, 'title' => $title, 'callback' => $callback, 'fields' => array());
	}

	/**
	 * Add a field to a section on the options page
	 *
	 * @param string $id ID attribute of the field
	 * @param string $title Title of the field.
	 * @param string $callback Function that prints the field itself.
	 * @param string $section Section of the settings page
	 * @param array $args Additional arguments (such as label_for and note)
	 */
	public function add_field($id, $title, $callback, $section = 'default', $args = array()) {
		if ( !isset($this->sections[$section]) ) {
			throw new Exception(_r('Invalid section.'));
			return false;
		}

		$this->sections[$section]['fields'][$id] = array('id' => $id, 'title' => $title, 'callback' => $callback, 'args' => $args);
	}

	/**
	 * Print the option sections and associated options
	 */
	public function do_sections() {
		if ( empty($this->sections) )
			return;

		foreach($this->sections as $id => $section) {
			echo '<fieldset id="' . $section['id'] . '">';
			echo '<legend>' . $section['title'] . '</legend>';
			call_user_func($section['callback'], $section);
			$this->do_fields($section);
			echo '</fieldset>';
		}
	}

	/**
	 * Print the option fields for a section
	 *
	 * @param string $section Section array as registered by add_section() and add_field()
	 */
	public function do_fields($section) {
		if ( empty($section['fields']) )
			return;

		foreach($section['fields'] as $field) {
			echo '<div class="row" id="' . $field['id'] . '">';

			if ( !empty($field['args']['label_for']) )
				echo '<label for="' . $field['args']['label_for'] . '">' . $field['title'] . ':</label>';
			else
				echo '<p class="title">' . $field['title'] . '</p>';

			call_user_func($field['callback'], $field['args']);

			if ( !empty($field['args']['note']) )
				echo '<p class="sidenote">' . $field['args']['note'] . '</p>';

			echo '</div>';
		}
	}
}