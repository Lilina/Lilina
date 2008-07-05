<?php
/**
 * Administration page
 * @todo Major cleanup of everything contained within and move to own folder
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

//
/**
 * Stop hacking attempts
 *
 * All included files (external libraries excluded) must check for presence of
 * this define (using defined() ) to avoid the files being accessed directly
 */
define('LILINA_ADMIN', 1) ;
define('LILINA_PATH', dirname(__FILE__));
define('LILINA_INCPATH', LILINA_PATH . '/inc');

//Protect from register_globals
$settings	= 0;
global $settings;

//Check installed
require_once(LILINA_INCPATH . '/core/install-functions.php');
lilina_check_installed();

$authed		= false;
$result		= '';
$page		= (isset($_REQUEST['page'])? $_REQUEST['page'] : '');
$page		= htmlspecialchars($page);

//Add variables

//Change variables
$change_name	= (isset($_REQUEST['change_name']))? $_REQUEST['change_name'] : '';
$change_name	= htmlspecialchars($change_name);
$change_url	= (isset($_REQUEST['change_url']))? $_REQUEST['change_url'] : '';
$change_id	= (isset($_REQUEST['change_id']))? $_REQUEST['change_id'] : '';
$change_id	= htmlspecialchars($change_id);

//Remove variables
$remove_id	= (isset($_REQUEST['remove']))? $_REQUEST['remove'] : '';
$remove_id	= htmlspecialchars($remove_id);

//Import variable
$import_url	= (isset($_REQUEST['import_url']))? $_REQUEST['import_url'] : '';
$import_url	= htmlspecialchars($import_url);

require_once(LILINA_INCPATH . '/core/plugin-functions.php');

//Localisation
require_once(LILINA_INCPATH . '/core/l10n.php');
require_once(LILINA_INCPATH . '/core/update-functions.php');

do_action('init');

/**
 * Contains all feed names, URLs and (eventually) categories
 * @global array $data
 */
$data		= file_get_contents(get_option('files', 'feeds')) ;
$data		= unserialize( base64_decode($data) ) ;
//Our current version
require_once(LILINA_INCPATH . '/core/version.php');

//For the RSS auto discovery
require_once(LILINA_INCPATH . '/core/feed-functions.php');

//Parse OPML files
require_once(LILINA_INCPATH . '/contrib/parseopml.php');
require_once(LILINA_INCPATH . '/core/auth-functions.php');

//Authentication Section
if(isset($_POST['user']) && isset($_POST['pass'])) {
	lilina_login_form($_POST['user'], $_POST['pass']);
}
else {
	lilina_login_form('', '');
}

if(isset($_REQUEST['logout']) && $_REQUEST['logout'] == 'logout') {
	lilina_logout();
	die();
}

/** This sanitises all input variables, so we don't have to worry about them later */
lilina_level_playing_field();



$admin_pages = array(
	'feeds' => '/pages/admin-feeds.php',
	'settings' => '/pages/admin-settings.php',
	'home' => '/pages/admin-home.php',
);
$admin_pages = apply_filters('admin_pages', $admin_pages, $page);

if(!isset($admin_pages[$page]))
	$page = 'home';

require_once(LILINA_INCPATH . $admin_pages[$page]);


switch($action){
	case 'change':
		$data['feeds'][$change_id]['feed'] = $change_url;
		if(!empty($change_name)) {
			$data['feeds'][$change_id]['name'] = $change_name;
		}
		else {
			//Need to have a similar function to add_feed()
		}
		$sdata	= base64_encode(serialize($data)) ;
		$fp		= fopen(get_option('files', 'feeds'),'w') ;
		if(!$fp) { echo 'Error';}
		fputs($fp,$sdata) ;
		fclose($fp) ;
		add_notice(sprintf(_r('Changed "%s" (#%d)'), $change_name, $change_id));
	break;
	case 'import':
	break;
	case 'reset':
		unlink(LILINA_PATH . '/conf/settings.php');
		printf(_r('settings.php successfully removed. <a href="%s">Reinstall</a>'), $_SERVER['PHP_SELF']);
		die();
	break;
}

function admin_header() {
	global $admin_pages, $page;
	$current_page = strtolower(basename($admin_pages[$page]));
	header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Admin Panel</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="inc/templates/default/admin.css" media="screen"/>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/humanmsg.js"></script>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/admin.js"></script>
</head>
<body id="admin-<?php echo $page; ?>" class="admin-page">
<div id="header">
	<p id="sitetitle"><a href="<?php echo get_option('baseurl'); ?>"><?php echo get_option('sitename'); ?></a></p>
	<div id="navigation">
		<a href="#main" id="skip">Skip to main content</a>
		<ul id="mainnavigation">
<?php
	$navigation = array(
		array(_r('Dashboard'), 'admin-home.php', ''),
		array(_r('Feeds'), 'admin-feeds.php', 'feeds'),
		array(_r('Settings'), 'admin-settings.php', 'settings'),
	);
	$navigation = apply_filters('navigation', $navigation);
	foreach($navigation as $nav_item) {
		echo '<li' . ($current_page == $nav_item[1] ? ' class="current"' : '') . "><a href='admin.php?page={$nav_item[2]}'>{$nav_item[0]}</a></li>";
	}
?>
			<li id="page_item_logout" class="seperator"><a href="admin.php?logout=logout" title="<?php _e('Log out of your current session'); ?>"><?php _e('Log out'); ?></a></li>
		</ul>
<?php

	$subnavigation = apply_filters('subnavigation', array(
		'admin-home.php' => array(
			array(_r('Home'), 'admin-home.php', 'home'),
		),
		'admin-feeds.php' => array(
			array(_r('Manage'), 'admin-feeds.php', 'feeds'),
		),
		'admin-settings.php' => array(
			array(_r('General'), 'admin-settings.php', 'settings'),
		),
	), $navigation, $current_page);

	if( isset($subnavigation[$current_page]) && !empty($subnavigation[$current_page]) ) {
?>
		<ul id="dropmenu">
<?php
		foreach($subnavigation[$current_page] as $subnav_item) {
			echo '<li' . ($current_page == $subnav_item[1] ? ' class="current"' : '') . "><a href='admin.php?page={$subnav_item[2]}'>{$subnav_item[0]}</a></li>";
		}
	}
?>
	</div>
</div>
<div id="main">
<?php
	if($result = apply_filters( 'alert_box', $result )) {
		echo '<div id="alert" class="fade">' . $result . '</div>';
	}
	do_action('admin_header');
	do_action("admin_header-$page");
	do_action('send_headers');
}

function admin_footer() {
?>
</div>
<p id="footer"><?php
_e('Powered by <a href="http://getlilina.org/">Lilina News Aggregator</a>');
do_action('admin_footer'); ?> | <a href="http://getlilina.org/docs/<?php _e('en'); ?>:start"><?php _e('Documentation');
?></a> and <a href="http://getlilina.org/forums/" title="<?php _e('Support on the Forums');?>"><?php _e('Support'); ?></a></p>
</body>
</html>
<?php
}
?>