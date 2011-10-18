<?php
/**
 * Common administration helpers
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */


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
<link rel="stylesheet" type="text/css" href="<?php echo get_option('baseurl'); ?>admin/resources/jquery-ui.css" media="screen"/>
<link rel="stylesheet" type="text/css" href="<?php echo get_option('baseurl'); ?>admin/resources/core.css" media="screen"/>
<link rel="stylesheet" type="text/css" href="<?php echo get_option('baseurl'); ?>admin/resources/full.css" media="screen"/>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/json2.js"></script>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/jquery.scrollTo.js"></script>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>admin/admin.js"></script>
<script type="text/javascript">
<?php
	$localisations = array(
		'nofeedurl'      => _r('No feed URL supplied'),
		'nofeedid'       => _r('No feed ID supplied'),
		'failedtoparse'  => _r('Failed to parse response: '),
		'ays'            => _r('Are You Sure?'),
		'whoops'         => _r('Whoops!'),
		'ok'             => _r('OK'),
		'cancel'         => _r('Cancel'),
		'delete'         => _r('Delete'),
		'somethingwrong' => _r('Something Went Wrong!'),
		'error'          => _r('Error message:'),
		'weirderror'     => _r('If you think you shouldn\'t have received this error then <a href="http://code.google.com/p/lilina/issues">report a bug</a> quoting that message and how it happened.'),
		'edithint'       => _r('Double-click to edit'),
		'delete'         => _r('Delete'),
		'showadvanced'   => _r('Show advanced options'),
		'dragme'         => _r('Drag this to your bookmarks bar'),
		'log'            => _r('Log'),
	);
?>
	admin.localisations = <?php echo json_encode($localisations) ?>;
</script>
</head>
<body id="admin-<?php echo basename($self, '.php'); ?>" class="admin-page">
<div id="header">
	<p id="sitetitle"><a href="<?php echo get_option('baseurl'); ?>"><?php echo get_option('sitename'); ?></a></p>
	<ul id="navigation">
<?php
	$navigation = array(
		array(_r('Dashboard'), 'index.php', ''),
		array(_r('Feeds'), 'feeds.php', 'feeds'),
		array(_r('Plugins'), 'plugins.php', 'plugins'),
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
		'plugins.php' => array(
			array(_r('Manage'), 'plugins.php', 'plugins'),
			//array(_r('Search & Install'), 'plugins-add.php', 'plugins'),
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
	</ul>
	<ul id="utilities">
		<li><a href="page_item_logout"><a href="login.php?logout" title="<?php _e('Log out of your current session'); ?>"><?php _e('Sign out'); ?></a></a></li>
		<?php do_action('admin_utilities_items'); ?>
	</ul>
</div>
<div id="main">
<?php
	if($result = implode('</p><p>', MessageHandler::get())) {
		echo '<div id="alert" class="fade"><p>' . $result . '</p></div>';
	}
	do_action('admin_header');
	do_action("admin_header-$self");
	do_action('send_headers');
}

function admin_footer() {
?>
</div>
<p id="footer"><?php
_e('Powered by <a href="http://getlilina.org/">Lilina</a>');
do_action('admin_footer'); ?> | <a href="http://getlilina.org/docs/start"><?php _e('Documentation') ?></a> | <a href="http://getlilina.org/forums/" title="<?php _e('Support on the Forums') ?>"><?php _e('Support') ?></a></p>
</body>
</html>
<?php
}
?>