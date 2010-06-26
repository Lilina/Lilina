<?php
/**
 * Common administration helpers
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * generate_nonce() - Generates nonce
 *
 * Uses the current time
 * @global array Need settings for user and password
 * @param string $nonce Supplied nonce
 * @return bool True if nonce is equal, false if not
 */
function generate_nonce() {
	$user_settings = get_option('auth');
	$time = ceil(time() / 43200);
	return md5($time . $user_settings['user'] . $user_settings['pass']);
}

/**
 * check_nonce() - Checks whether supplied nonce matches current nonce
 * @global array Need settings for user and password
 * @param string $nonce Supplied nonce
 * @return bool True if nonce is equal, false if not
 */
function check_nonce($nonce) {
	$user_settings = get_option('auth');
	$time = ceil(time() / 43200);
	$current_nonce = md5($time . $user_settings['user'] . $user_settings['pass']);
	if($nonce !== $current_nonce) {
		return false;
	}
	return true;
}


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
	admin.localisations = {
		"No feed URL supplied": "<?php _e('No feed URL supplied') ?>",
		"No feed ID supplied": "<?php _e('No feed ID supplied') ?>",
		"Failed to parse response: ": "<?php _e('Failed to parse response: ') ?>",
		"Are You Sure?": "<?php _e('Are You Sure?') ?>",
		"Whoops!": "<?php _e('Whoops!') ?>",
		"OK": "<?php _e('OK') ?>",
		"Cancel": "<?php _e('Cancel') ?>",
		"Something Went Wrong!": "<?php _e('Something Went Wrong!') ?>",
		"Error message:": "<?php _e('Error message:') ?>",
		'If you think you shouldn\'t have received this error then <a href="http://code.google.com/p/lilina/issues">report a bug</a> quoting that message and how it happened.': '<?php echo str_replace("'", '\\\'', _r('If you think you shouldn\'t have received this error then <a href="http://code.google.com/p/lilina/issues">report a bug</a> quoting that message and how it happened.')) ?>',
		"Double-click to edit": "<?php _e('Double-click to edit') ?>",
		"Delete": "<?php _e('Delete') ?>",
		"Show advanced options": "<?php _e('Show advanced options') ?>"
	};
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
_e('Powered by <a href="http://getlilina.org/">Lilina News Aggregator</a>');
do_action('admin_footer'); ?> | <a href="http://getlilina.org/docs/start"><?php _e('Documentation') ?></a> | <a href="http://getlilina.org/forums/" title="<?php _e('Support on the Forums') ?>"><?php _e('Support') ?></a></p>
</body>
</html>
<?php
}
?>