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
<h1>Settings</h1>
<?php
//Defined in admin panel
$list			= '<table id="feeds_list">
<tr class="row_header row">
<th class="col_even col">Setting Name</th>
<th class="col_odd col">Setting Value</th>
</tr>';
$num			= 'odd';
//Uses a for loop instead of a foreach, so we can
//get the current id
$j	= 0;
foreach($settings as $key => $value) {
	$list		.= '<tr class="row_' . $num . ' row">
	<td class="col_even col">'.$key.'</td>
	<td class="col_odd col">'.$value.'</td>
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
<h2>Current Settings</h2>
<?php
echo $list;
?>
<h2>Troubleshooting</h2>
<a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=settings&amp;action=diagnostic">Run diagnostic test</a>
<h2>Reset</h2>
<p>This will delete your settings.php and you will need to run install.php again. <a href="<?php echo $_SERVER['PHP_SELF'];?>?page=settings&amp;action=reset">Proceed?</a></p>