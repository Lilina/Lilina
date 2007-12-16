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

defined('LILINA') or die('Restricted access');
$settings = 0;
global $settings;
$settings							= array();

//Must be in seconds
$settings['cachetime']				= 600;

/**
 * GZip output
 *
 * Make sure this is disabled if your PHP version is less than
 * 4.0.5 or if you have zlib.output_compression enabled in your
 * php.ini
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
$settings['lang']					= 'en';
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

require_once(LILINA_PATH . '/conf/settings.php') ;

//Settings that use other settings variables, can not be overriden
$settings['cachedir']				= $settings['path'] . '/cache/';
$settings['files']					= array(
											'feeds'		=> $settings['path'] . '/conf/feeds.data',
											'times'		=> $settings['path'] . '/conf/time.data',
											'settings'	=> $settings['path'] . '/conf/settings.php',
											'plugins'	=> $settings['path'] . '/conf/plugins.data'
											);

$plugins							= '';

//$new_settings = array_diff($default_settings, $settings);

//--------------------
//Old stuff
/*

/*
  Show Social Bookmarks
  Default is true

$SHOW_SOCIAL = true;
/* 
	IMPORTANT NOTE! Setting ENABLE_DELICIOUS to 1 will make lilina poll del.icio.us for tags.
	THIS MAY RESULT TO DEL.ICIO.US BANNING YOUR IP!!!
	Until del.icio.us officially allows such use, it is better to leave this to 0.

$ENABLE_DELICIOUS = 0 ;
/*
  Open Social Bookmark links in a new window
  Default is true

$SOCIAL_NEW = true;

///////////////////
// Cache Options //
///////////////////

//Favicon cache time
$cachetime = 7 * 24 * 60 * 60; // 7 days
///////////////////
// Other Options //
///////////////////
/*
  RSS Output Enabling
  Default setting is on
  Set to 0 to disable

$RSS_OUTPUT = 1;
////////////////////
//////Framework/////
////////////////////
/* This does nothing *yet*
$CATAGORYFILE = './.catagories.data';
*/
?>