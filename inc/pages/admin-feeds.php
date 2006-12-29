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
<h1>Feeds</h1>
<?php
//Defined in admin panel
$feeds = get_feeds();
$list = '<table>
<tr>
<th>Feed Name</th>
<th>URL</th>
<th>Remove Feed</th>
<th>Change Feed</th>
</tr>';
for($feed = 0; $feed < count($feeds); $feed++) {
	$this_feed = $feeds[$feed];
	$list .= '<tr>
	<td>'.$this_feed['name'].'</td>
	<td>'.$this_feed['link'].'</td>
	<td><input type="checkbox" name="remove-'.$this_feed['id'].'" /></td>
	<td><input type="checkbox" name="change-'.$this_feed['id'].'" /></td>
	</tr>';
}
$list .= '</table>';
?>
<h2>Current Feeds</h2>
<form method="post">
<?php
echo $list;
?>
<input type="submit" label="Submit Changes" />
</form>
<h2>Add Feeds</h2>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
Display Name: <input type="text" name="name" style="width: 14em;" />
URL to Feed: <input type="text" name="url" style="width: 14em;" />
<input type="submit" value="Add Feed" />
</form>