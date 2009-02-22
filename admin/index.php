<?php
/**
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
require_once('admin.php');

admin_header(_r('Home'));
?>
<h1><?php _e('Admin Panel') ?></h1>
<p><?php _e('Welcome to your administration panel for Lilina! You can do a few things here:') ?></p>
<ul>
	<li><a href="feeds.php"><?php _e('Edit your feeds') ?></a></li>
	<li><a href="settings.php"><?php _e('Change your settings') ?></a></li>
</ul>
<div class="dashbox" id="contain_feeds">
	<h3><?php _e('Current Feeds') ?></h3>
	<ul>
<?php
/**
 * 
 * @global array
 */
function get_feed_list() {
	global $data;
	if(isset($data['feeds']))
		return $data['feeds'];
	return false;
}
$feed_list = get_feed_list();
if(is_array($feed_list)) {
	foreach($feed_list as $this_feed) {
		if(isset($this_feed['name']) && !empty($this_feed['name'])) {
			echo '
	<li><a href="' . $this_feed['feed'] . '">'.stripslashes($this_feed['name']).'</a></li>';
		}
		else {
			echo '
	<li><a href="' . $this_feed['feed'] . '">' . _r('(No title specified)') . '</a></li>';
		}
	}
}
else {
	echo '<li>' . _r('No feeds installed yet.') . '</li>';
}
?>
	</ul>
	<h3><a href="feeds.php"><?php _e('Edit your feeds') ?></a></h3>
</div>

<div class="dashbox" id="contain_settings">
	<h3><a href="settings.php"><?php _e('Change your settings') ?></a></h3>
</div>
<?php
admin_footer();
?>