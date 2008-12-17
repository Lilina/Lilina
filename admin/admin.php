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
define('LILINA_PATH', dirname(dirname(__FILE__)));
define('LILINA_INCPATH', LILINA_PATH . '/inc');
define('LILINA_ADMIN', 1) ;

//Protect from register_globals
$settings	= 0;
global $settings;

//Check installed
require_once(LILINA_INCPATH . '/core/install-functions.php');
lilina_check_installed();

require_once(LILINA_INCPATH . '/core/plugin-functions.php');

//require_once(LILINA_INCPATH . '/core/l10n.php');
Locale::load_default_textdomain();
require_once(LILINA_INCPATH . '/core/update-functions.php');

do_action('admin_init');
do_action('init');

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

function admin_header($title, $parent_file = false) {
	$self = preg_replace('|^.*/admin/|i', '', $_SERVER['PHP_SELF']);
	$self = preg_replace('|^.*/plugins/|i', '', $self);

	header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $title ?> &mdash; <?php echo get_option('sitename'); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="<?php echo get_option('baseurl'); ?>admin/admin.css" media="screen"/>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/jquery.scrollTo.js"></script>
<!--<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/jquery.json.js"></script>-->
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/humanmsg.js"></script>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>admin/admin.js"></script>
</head>
<body id="admin-<?php echo $self; ?>" class="admin-page">
<div id="header">
	<p id="sitetitle"><a href="<?php echo get_option('baseurl'); ?>"><?php echo get_option('sitename'); ?></a></p>
	<ul id="navigation">
<?php
	$navigation = array(
		array(_r('Dashboard'), 'index.php', ''),
		array(_r('Feeds'), 'feeds.php', 'feeds'),
		array(_r('Settings'), 'settings.php', 'settings'),
	);
	$navigation = apply_filters('navigation', $navigation);

	$subnavigation = apply_filters('subnavigation', array(
		'index.php' => array(
			array(_r('Home'), 'index.php', 'home'),
		),
		'feeds.php' => array(
			array(_r('Add/Manage'), 'feeds.php', 'feeds'),
			array(_r('Import'), 'feed-import.php', 'feeds'),
		),
		'settings.php' => array(
			array(_r('General'), 'settings.php', 'settings'),
		),
	), $navigation, $self);

	foreach($navigation as $nav_item) {
		$class = 'item';
		if((strcmp($self, $nav_item[1]) == 0) || ($parent_file && ($nav_item[1] == $parent_file))) {
			$class .= ' current';
		}

		if(isset($subnavigation[$nav_item[1]]) && count($subnavigation[$nav_item[1]]) > 1)
			$class .= ' has-submenu';

		echo "<li class='$class'><a href='{$nav_item[1]}'>{$nav_item[0]}</a>";
		
		if(!isset($subnavigation[$nav_item[1]]) || count($subnavigation[$nav_item[1]]) < 2) {
			echo "</li>";
			continue;
		}
		
		echo '<ul class="submenu">';
		foreach($subnavigation[$nav_item[1]] as $subnav_item) {
			echo '<li' . ((strcmp($self, $subnav_item[1]) == 0) ? ' class="current"' : '') . "><a href='{$subnav_item[1]}'>{$subnav_item[0]}</a></li>";
		}
		echo '</ul></li>';
		
	}
?>
			<li id="page_item_logout" class="seperator"><a href="admin.php?logout=logout" title="<?php _e('Log out of your current session'); ?>"><?php _e('Log out'); ?></a></li>
	</ul>
</div>
<div id="main">
<?php
	if($result = implode('</p><p>', MessageHandler::get())) {
		echo '<div id="alert" class="fade"><p>' . $result . '</p></div>';
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
do_action('admin_footer'); ?> | <a href="http://getlilina.org/docs/start"><?php _e('Documentation') ?></a> | <a href="http://getlilina.org/forums/" title="<?php _e('Support on the Forums') ?>"><?php _e('Support') ?></a></p>
</body>
</html>
<?php
}
?>