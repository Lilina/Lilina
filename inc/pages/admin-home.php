<?php
/**
 * @todo Move to admin/index.php
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

defined('LILINA_PATH') or die('Restricted access');
admin_header();
?>
<h2>Admin Panel</h2>
<div class="home_container" id="contain_feeds">
	<h3>Current feeds</h3>
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
	<li><a href="' . $this_feed['feed'] . '">(No title specified)</a></li>';
		}
	}
}
else {
	echo '<li>No feeds installed yet.</li>';
}
?>
	</ul>
	<h3><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=feeds">Add, remove and change feeds</a></h3>
</div>

<div class="home_container" id="contain_settings">
	<h3><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=settings">Change your settings</a></h3>
</div>
<?php
admin_footer();
?>