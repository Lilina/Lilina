<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		admin-feeds.php
Purpose:	Feeds admin page
Notes:		Used only in admin.php
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
defined('LILINA') or die('Restricted access');
?>
<h1>Admin Panel</h1>
<div class="home_container" id="contain_feeds">
	<h2>Current feeds</h2>
	<ul>
	<?php
	foreach(get_feed_list() as $this_feed) {
		if(isset($this_feed['name']) && !empty($this_feed['name'])) {
			echo '
		<li><a href="' . $this_feed['feed'] . '">'.$this_feed['name'].'</a></li>';
		}
		else {
			echo '
		<li><a href="' . $this_feed['feed'] . '">(No title specified)</a></li>';
		}
	}
	?>
	</ul>
	<h3><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=feeds">Add, remove and change feeds</a></h3>
</div>

<div class="home_container" id="contain_settings">
	<h2><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=settings">Change your settings</a></h3>
</div>