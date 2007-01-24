<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		conf.php
Purpose:	Default Configuration
Notes:		Need to move all crud to plugins
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
defined('LILINA') or die('Restricted access');
$settings = 0;
global $settings;
$settings							= array();
$settings							= file('./inc/settings.php');
//Must be in seconds
$settings['cachetime']				= 600;
//Magpie cache time is default
$settings['magpie']					= array('cachetime' => 3600);
$settings['baseurl']				= 'http://localhost/';
//No need to change this really
$settings['path']					= dirname(dirname(dirname(__FILE__)));
$settings['template']				= 'default';
$settings['template_path']			= $settings['baseurl'] . 'templates/' . $settings['template'];
$settings['cachedir']				= $settings['path'] . '/cache/';
$settings['sitename']				= 'Lilina News Aggregator';
$settings['auth']					= array('user' => 'username', 'pass' => 'password');
$settings['owner']					= array('name' => 'Bob Smith', 'email' => 'bsmith@example.com');
$settings['lang']					= 'english';
$settings['files']					= array(
											'feeds'		=> $settings['path'] . '/feeds.data',
											'times'		=> $settings['path'] . '/time.data',
											'settings'	=> $settings['path'] . '/settings.data',
											'plugins'	=> $settings['path'] . '/plugins.data'
											);
$settings['feeds']					= array('items' => '25');
//Default time is always the first time
//Numbers are hours, valid string values are 'week' and 'all'
$settings['interface']				= array('times' => array(24,48,'week','all'), 'template' => 'default');
$settings['output']					= array(
											'rss' => true,
											'opml' => true,
											'html' => true,
											'atom' => true
											);
//Default: 0 hours
//Timezone offset
$settings['offset']					= 0;
$settings['encoding']				= 'utf-8';
//Debug mode?
$settings['debug']					= 'false';

//Backup our settings
$default_settings					= $settings;

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