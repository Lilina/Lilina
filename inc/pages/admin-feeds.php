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
<th class="col_odd col">Feed Name</th>
<th class="col_even col">URL</th>
<th class="col_odd col">Remove Feed</th>
<th class="col_even col">Change Feed</th>
</tr>';
$num			= 'odd';
foreach($feeds as $this_feed) {
	$list		.= '<tr class="row_' . $num . ' row">
	<td class="col_odd col">'.$this_feed['name'].'</td>
	<td class="col_even col">'.$this_feed['feed'].'</td>
	<td class="col_odd col"><input type="checkbox" name="remove-'.$this_feed['id'].'" /></td>
	<td class="col_even col"><input type="checkbox" name="change-'.$this_feed['id'].'" /></td>
	</tr>';
	if($num=='odd'){
		$num	= 'even';
	}
	else {
		$num	= 'odd';
	}
}
$list .= '</table>';
?>
<h2>Current Feeds</h2>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<?php
echo $list;
?>
<input type="hidden" name="page" value="feeds" />
<input type="submit" value="Submit Changes" />
</form>
<h2>Add Feeds</h2>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
<label for="name">Display Name:</label> <input type="text" name="name" id="url" style="width: 14em;" />
<label for="url">URL to Feed:</label> <input type="text" name="url" id="url" style="width: 14em;" />
<input type="hidden" name="page" value="feeds" />
<input type="submit" value="Add Feed" />
</form>