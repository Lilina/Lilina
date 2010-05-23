<?php
/**
 * Template handler
 *
 * @package Lilina
 * @subpackage Templates
 */

/**
 * Template handler
 *
 * @package Lilina
 */
class Templates {
	/**
	 * Holds all current theme names and data
	 * @var array
	 */
	private static $templates = array();

	/**
	 * Holds all broken theme names and data
	 * @var array
	 */
	private static $borked = array();

	/**
	 * Returns the current template directory
	 *
	 * @since 1.0
	 * @see get_option
	 * @todo This is dirrrty dirrrty code. Fix ASAP.
	 *
	 * @param string $view Name of the current view
	 * @param string $prefix String to prefix to the cache ID, defaults to template name
	 * @return string
	 */
	public static function load($view = 'chrono', $prefix = '') {
		if(empty($prefix))
			$prefix = get_option('template');

		$current = Templates::get_current();
		$view_file = $view . '.php';
		Templates::headers();
		$cache = new CacheHandler();
		$cache->begin_caching($prefix . $_SERVER['REQUEST_URI']);
		if(file_exists($current['Template Dir'] . '/' . $view_file))
			require_once($current['Template Dir'] . '/' . $view_file);
		else
			require_once($current['Template Dir'] . '/index.php');
		$cache->end_caching($prefix . $_SERVER['REQUEST_URI']);
	}

	/**
	 * HTTP header handler
	 *
	 * Handles the output of all default headers, but can be changed via the
	 * `template-headers` filter.
	 *
	 * Also controls working with If-None-Match/If-Modified-Since
	 */
	public static function headers() {
		// Basic default headers
		$headers = array(
			'Content-Type' => 'text/html; charset=utf-8',
		);

		// Last-Modified and ETag
		$itemcache = Items::get_instance();
		$itemcache->init();
		$item = reset($itemcache->retrieve());

		if($item !== false)
			$last_modified_timestamp = $item->timestamp;
		else
			$last_modified_timestamp = time();

		$last_modified = date('D, d M Y H:i:s', $last_modified_timestamp);
		$headers['Last-Modified'] = $last_modified . ' GMT';
		$headers['ETag'] = '"' . md5($last_modified) . '"';

		// Do the header dance
		$headers = apply_filters('template-headers', $headers);
		foreach($headers as $name => $value) {
			header($name . ': ' . $value);
		}

		// Conditional GET
		if (isset($_SERVER['HTTP_IF_NONE_MATCH']))
			$client_etag = stripslashes($_SERVER['HTTP_IF_NONE_MATCH']);
		else
			$client_etag = false;

		$client_modified = (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) ? strtotime(trim($_SERVER['HTTP_IF_MODIFIED_SINCE'])) : false;

		$protocol = $_SERVER["SERVER_PROTOCOL"];
		if ( 'HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol )
			$protocol = 'HTTP/1.0';

		if ($client_modified !== false && $client_etag !== false) {
			if(($client_modified >= $last_modified_timestamp) && ($client_etag == $headers['ETag'])) {
				header($protocol . ' 304 Not Modified');
				die();
			}
		}
		elseif(($client_modified >= $last_modified_timestamp) || ($client_etag == $headers['ETag'])) {
			header($protocol . ' 304 Not Modified');
			die();
		}
	}

	/**
	 * Run the initialisation file in the template's directory
	 *
	 * Enables template authors to put all of their PHP code in one file to be
	 * run on init, without worrying about hooking it. Works like WordPress's
	 * functions.php
	 */
	public static function init_template() {
		$current = Templates::get_current();

		if (file_exists($current['Stylesheet Dir'] . '/init.php') && $current['Stylesheet Dir'] != $current['Template Dir'])
			include($current['Stylesheet Dir'] . '/init.php');

		if (file_exists($current['Template Dir'] . '/init.php'))
			include($current['Template Dir'] . '/init.php');
	}

	/**
	 * Returns the path to a specified file
	 *
	 * Uses content negotiation to find the best suitable match for $file_name
	 * @param string $file_name Filename to attempt to find
	 * @return string|boolean Path to file found, false if none found
	 */
	public static function get_file($file_name) {
		$current = Templates::get_current();

		if (file_exists($current['Stylesheet Dir'] . '/' . $file_name))
			return $current['Stylesheet Dir'] . '/' . $file_name;

		elseif (file_exists($current['Template Dir'] . '/' . $file_name))
			return $current['Template Dir'] . '/' . $file_name;

		elseif (file_exists(Templates::get_template_root() . '/default/' . $file_name))
			return Templates::get_template_root() . '/default/' . $file_name;

		else
			return false;
	}

	/**
	 * Convert a template path to a URL
	 *
	 * @param string $path
	 * @return string
	 */
	public static function path_to_url($path) {
		return str_replace(Templates::get_template_root(), get_option('baseurl') . 'inc/templates', $path);
	}

	/**
	 * Returns the template root directory
	 *
	 * @return string
	 */
	public static function get_template_root() {
		return LILINA_INCPATH . '/templates';
	}

	/**
	 * Returns the current template directory
	 *
	 * @return string
	 */
	public static function get_template_dir() {
		return LILINA_INCPATH . '/templates/' . get_option('template');
	}

	/**
	 * Returns the current template data
	 * @return string
	 * @see get_option
	 */
	public static function get_current() {
		if( !$data = Templates::get_template_data(get_option('template')) )
			$data = Templates::get_template_data('default');

		return $data;
	}

	/**
	 * Get theme metadata for a specific file
	 *
	 * Based on code from WordPress
	 * @author WordPress
	 * @param string $theme_file Absolute location of file to retrieve metadata from
	 * @return array Metadata
	 */
	public static function get_file_data($theme_file) {
		$info = array(
			'Author' => _r('Anonymous'),
			'Author URI' => '',
			'Description' => '',
			'Parent' => '',
			'Theme Name' => '',
			'Theme URI' => '',
			'Version' => ''
		);
		$theme_data = file_get_contents( $theme_file );
		$theme_data = str_replace ( '\r', '\n', $theme_data );
		
		foreach (array('Author', 'Author URI', 'Description', 'Parent', 'Theme Name', 'Theme URI', 'Version') as $key) {
			if ( preg_match( '|' . $key . ':(.*)$|mi', $theme_data, $data ) )
				$info[$key] = $data[1];
		}
		
		$info = array_map('trim', $info);
		$info['Tags'] = array();
		
		if ( preg_match('|Tags:(.*)|i', $theme_data, $tags) )
			$info['Tags'] = array_map('trim', explode( ',', trim( $tags[1] )));
		
		//Sanitize::clean_local($info);
		return $info;
	}
	
	/**
	 * Retrieve all template data and names
	 *
	 * @return array Template metadata
	 */
	public static function get_templates($get_single = false) {
		if (!empty(Templates::$templates) )
			return Templates::$templates;

		$files = array();
		$borked = array();
		$template_root = Templates::get_template_root();

		$directory = opendir($template_root);
		if(empty($directory))
			return array();

		$directory = glob($template_root . '/*/style.css');
		
		foreach($directory as $dir) {
			$files[] = basename(dirname($dir));
		}
		
		sort($files);

		foreach ( $files as $template ) {
			Templates::get_template_data($template);
		}

		Templates::$borked = array_merge(Templates::$borked, $borked);
		return Templates::$templates;
	}

	/**
	 * Get template data for a single template
	 *
	 * @param string $template Template to retrieve data for
	 * @return array Template data
	 */
	public static function get_template_data($template) {
		$template_root = Templates::get_template_root();

		if ( !is_readable("$template_root/$template/") ) {
			Templates::$borked[$template] = array('File' => $template, 'Error' => _r('File not readable.'));
			return false;
		}

		$theme_data = Templates::get_file_data("$template_root/$template/style.css");
		$name        = $theme_data['Theme Name'];
		$parent	     = $theme_data['Parent'];

		if( empty($name) )
			$name = $template;
		if( empty($parent) )
			$parent = $template;

		$screenshot = false;
		foreach ( array('png', 'gif', 'jpg', 'jpeg') as $ext ) {
			if (file_exists("$template_root/$template/screenshot.$ext")) {
				$screenshot = "screenshot.$ext";
				break;
			}
		}

		if ( !file_exists("$template_root/$parent/index.php") ) {
			$parent_dir = dirname(dirname($template));
			if ( file_exists("$template_root/$parent_dir/$template/index.php") ) {
				$parent = "$template";
			} else {
				Templates::$borked[$name] = array('Name' => $name, 'File' => $template, 'Description' => _r('Parent is missing.'));
				return false;
			}
		}

		$stylesheet_files = array();
		$template_files = array();

		$stylesheet_dir = glob("$template_root/$template/*.*");
		if ( !empty($stylesheet_dir) ) {
			foreach($stylesheet_dir as &$file) {
				if ( preg_match('|\.css$|', $file) )
					$stylesheet_files[] = $file;
				elseif ( preg_match('|\.php$|', $file) )
					$template_files[] = $file;
			}
		}

		$template_files = array_merge(glob("$template_root/$parent/*.php"), $template_files);

		$template_dir = dirname($template_files[0]);
		$stylesheet_dir = dirname($stylesheet_files[0]);

		if ( empty($template_dir) )
			$template_dir = '/';
		if ( empty($stylesheet_dir) )
			$stylesheet_dir = '/';

		return Templates::$templates[$template] = array(
			'Name' => $name,
			'Description' => $theme_data['Description'],
			'Author' => $theme_data['Author'],
			'Version' => $theme_data['Version'],
			'Parent' => $parent,
			'Stylesheet' => $template,
			'Template Files' => array_unique($template_files),
			'Stylesheet Files' => array_unique($stylesheet_files),
			'Template Dir' => $template_dir,
			'Stylesheet Dir' => $stylesheet_dir,
			'Screenshot' => $screenshot,
			'Tags' => $theme_data['Tags']
		);
	}
}
