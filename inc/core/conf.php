<?php
/**
 * Default configuration
 *
 * Default settings stored in the global $settings variable
 * DO NOT MAKE CHANGES IN THIS FILE, AS THEY WILL BE OVERRIDDEN WHEN YOU UPDATE
 * Instead, make changes in /conf/settings.php and copy needed settings over.
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

	//Must be in seconds
	$settings['cachetime']				= 600;

	/*
	GZip output
	Make sure this is disabled if your PHP version is less than
	4.0.5 or if you have zlib.output_compression enabled in your
	php.ini
	*/
	$settings['gzip']					= false;
	//Magpie cache time is default
	$settings['magpie']					= array('cachetime' => 3600);

	$settings['baseurl']				= 'http://localhost/';
	//No need to change this really
	$settings['path']					= LILINA_PATH;
	//Name of template
	$settings['template']				= 'default';
	$settings['sitename']				= 'Lilina News Aggregator';
	$settings['auth']					= array('user' => 'username', 'pass' => 'password');
	$settings['owner']					= array('name' => 'Bob Smith', 'email' => 'bsmith@example.com');
	$settings['locale']					= 'en';
	//Maximum number of items from each feed, 0 is unlimited
	$settings['feeds']					= array('items' => '25');
	//Default time is always the first time
	//Numbers for hours or 'week' for a week
	//'all' is automatically added
	$settings['interface']				= array('times' => array(24,48,'week'));
	//Output types
	$settings['output']					= array(
												'rss' => true,
												'html' => true,
												'atom' => true
												);
	//Timezone offset
	//Note: difference between your time and your server's time
	$settings['offset']					= 0;
	$settings['encoding']				= 'utf-8';
	//Debug mode?
	$settings['debug']					= 'false';
	//Just in case we need to check against them
	$default_settings = $settings;

	/**
	 * Holds all the users settings
	 *
	 * Holds the new $settings variables which overwrites
	 * all our old settings here.
	 */
	require_once(LILINA_PATH . '/conf/settings.php') ;

	/**
	 * Unserialize any settings which were serialized, e.g. objects
	 */
	foreach($settings as $key => $the_setting) {
		$settings[$key] = maybe_unserialize($the_setting);
	}
	
	/**
	 * Stores the location of the language directory. First looks for language folder in wp-content
	 * and uses that folder if it exists. Or it uses the "languages" folder in LILINA_INCPATH.
	 *
	 * @since 1.0.0
	 */
	if ( !defined('LANGDIR'))
		define('LANGDIR', '/inc/locales');

	//Settings that use other settings variables, can not be overriden
	$settings['cachedir']				= $settings['path'] . '/cache/';
	$settings['files']					= array(
												'feeds'		=> $settings['path'] . '/conf/feeds.data',
												'times'		=> $settings['path'] . '/conf/time.data',
												'settings'	=> $settings['path'] . '/conf/settings.php',
												'plugins'	=> $settings['path'] . '/conf/plugins.data'
												);

	$plugins							= '';
}
?>