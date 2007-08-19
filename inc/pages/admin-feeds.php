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
$feeds			= get_feeds();
$list			= '<table id="feeds_list">
<tr class="row_header row">
<th class="col_even col">Feed ID</th>
<th class="col_odd col">Feed Name</th>
<th class="col_even col">URL</th>
<th class="col_odd col">Remove Feed</th>
<th class="col_even col">Change Feed</th>
</tr>';
$num			= 'odd';
//Uses a for loop instead of a foreach, so we can
//get the current id
$j	= 0;
foreach($feeds as $this_feed) {
	$list		.= '<tr class="row_' . $num . ' row">
	<td class="col_even col">'.$j.'</td>
	<td class="col_odd col">'.$this_feed['name'].'</td>
	<td class="col_even col">'.$this_feed['feed'].'</td>
	<td class="col_odd col"><a href="' . $_SERVER['PHP_SELF'] . '?page=feeds&amp;remove=' . $j . '&amp;action=remove">Remove</a></td>
	<td class="col_even col"><a href="' . $_SERVER['PHP_SELF'] . '?page=feeds&amp;change=' . $j .'&amp;action=change" onclick="javascript:return showChange(\''.$j.'\', \''.$this_feed['name'].'\', \''.$this_feed['feed'].'\');">Change</a></td>
	</tr>';
	if($num=='odd'){
		$num	= 'even';
	}
	else {
		$num	= 'odd';
	}
	++$j;
}
$list .= '</table>';
?>
<h2>Current Feeds</h2>
<?php
echo $list;
?>
<div id="changer">
	<h2>Change Feeds</h2>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
		<label for="change_name">Display Name:</label>
		<input type="text" name="change_name" id="change_name" style="width: 14em;" />
		<label for="change_url">URL to Feed:</label>
		<input type="text" name="change_url" id="change_url" style="width: 14em;" />
		<input type="hidden" name="page" value="feeds" />
		<input type="hidden" name="action" value="change" />
		<div id="changer_id">
			<label for="change_id">Feed ID:</label>
			<input type="text" name="change_id" id="change_id" value="" />
		</div>
		<input type="submit" value="Change Feed" />
	</form>
</div>
<h2>Add Feeds</h2>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
<label for="add_name">Display Name:</label> <input type="text" name="add_name" id="add_name" style="width: 14em;" />
<label for="add_url">URL to Feed:</label> <input type="text" name="add_url" id="add_url" style="width: 14em;" />
<input type="hidden" name="page" value="feeds" />
<input type="hidden" name="action" value="add" />
<input type="submit" value="Add Feed" />
</form>
<p>If no name is specified, it will be taken from the feed</p>