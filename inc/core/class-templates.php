<?php
/**
 *
 * @package Lilina
 * @subpackage Templates
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
	 * @todo This is dirrrty dirrrty code. Fix ASAP.
	 * @return string
	 * @see get_option
	 */
	public static function load($view = 'chrono', $meta = '') {
		$current = Templates::get_current();
		$view_file = $view . '.php';
		if(file_exists($current['Template Dir'] . '/' . $view_file))
			require_once($current['Template Dir'] . '/' . $view_file);
		else
			require_once($current['Template Dir'] . '/index.php');
	}

	/**
	 * Returns the current template directory
	 *
	 * @return string
	 * @see get_option
	 */
	public static function get_template_root() {
		return LILINA_INCPATH . '/templates';
	}

	/**
	 * Returns the current template directory
	 *
	 * @return string
	 * @see get_option
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
		Templates::get_templates();
		return Templates::$templates[get_option('template')];
	}

	/**
	 * Get theme metadata for a specific file
	 *
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
		$theme_data = implode( '', file( $theme_file ) );
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
	 * Based on code from WordPress
	 * @return array Template metadata
	 * @author WordPress
	 */
	public function get_templates() {
		if (!empty(Templates::$templates) )
			return Templates::$templates;

		$files = array();
		$borked = array();
		$template_root = Templates::get_template_root();

		$directory = opendir($template_root);
		if(!$directory)
			return array();

		while (($dir = readdir($directory)) !== false) {
			if ( is_dir($template_root . '/' . $dir) && is_readable($template_root . '/' . $dir) ) {
				if ( $dir[0] == '.' || $dir == 'CVS')
					continue;
				
				$subdir = @ opendir($template_root . '/' . $dir);
				$continue = false;
				
				while ( ($subdir_file = readdir($subdir)) !== false ) {
					if ( $subdir_file == 'style.css' ) {
						$files[] = $dir;
						$continue = true;
						break;
					}
				}
				@closedir($subdir);
				
				if($continue)
					continue;
				
				$borked[$dir] = array('File' => $template_root . '/' . $dir, 'Error' => _r('Stylesheet not found.'));
			}
		}
		if ( is_dir( $directory ) )
			@closedir( $directory );
		if (!$files )
			return array();
		
		sort($files);

		foreach ( $files as $template ) {
			if ( !is_readable("$template_root/$template/") ) {
				$borked[$template] = array('File' => $template, 'Error' => _r('File not readable.'));
				continue;
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
					$borked[$name] = array('Name' => $name, 'File' => $template, 'Description' => _r('Parent is missing.'));
					continue;
				}
			}

			$stylesheet_files = array();
			$template_files = array();

			$stylesheet_dir = dir("$template_root/$template");
			if ( $stylesheet_dir ) {
				while ( ($file = $stylesheet_dir->read()) !== false ) {
					if ( !preg_match('|^\.+$|', $file) ) {
						if ( preg_match('|\.css$|', $file) )
							$stylesheet_files[] = "$template_root/$template/$file";
						elseif ( preg_match('|\.php$|', $file) )
							$template_files[] = "$template_root/$template/$file";
					}
				}
			}

			$template_dir = dir("$template_root/$parent");
			if ( $template_dir ) {
				while(($file = $template_dir->read()) !== false) {
					if ( !preg_match('|^\.+$|', $file) && preg_match('|\.php$|', $file) )
						$template_files[] = "$template_root/$parent/$file";
				}
			}

			$template_dir = dirname($template_files[0]);
			$stylesheet_dir = dirname($stylesheet_files[0]);

			if ( empty($template_dir) )
				$template_dir = '/';
			if ( empty($stylesheet_dir) )
				$stylesheet_dir = '/';

			Templates::$templates[$template] = array(
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

		Templates::$borked = $borked;
		return Templates::$templates;
	}
}