<?php
/**
 * Feeds page
 * @todo Move to admin/feeds.php
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

defined('LILINA') or die('Restricted access');
?>
<h2><?php _e('Feeds'); ?></h2>
<?php
//Defined in admin panel
$feeds			= get_feed_list();
$list			= '<table id="feeds_list">
<thead>
	<tr>
	<th>' . _r('Feed ID') . '</th>
	<th >' . _r('Feed Name') . '</th>
	<th>' . _r('URL') . '</th>
	<th class="change-col">' . _r('Change Feed') . '</th>
	<th class="remove-col">'. _r ('Remove Feed') . '</th>
	</tr>
</thead>
<tbody>';
$alt			= '';
//Uses a for loop instead of a foreach, so we can
//get the current id
$j	= 0;
if(is_array($feeds)) {
	foreach($feeds as $this_feed) {
		$list		.= '<tr class="' . $alt . '">
		<td class="id-col">'.$j.'</td>
		<td class="name-col">'.$this_feed['name'].'</td>
		<td class="url-col">'.$this_feed['feed'].'</td>
		<td class="change-col"><a href="' . $_SERVER['PHP_SELF'] . '?page=feeds&amp;change=' . $j .'&amp;action=change" class="change_link">Change</a></td>
		<td class="remove-col"><a href="' . $_SERVER['PHP_SELF'] . '?page=feeds&amp;remove=' . $j . '&amp;action=remove">Remove</a></td>
		</tr>';
		$alt = empty($alt) ? 'alt' : '';
		++$j;
	}
}
else {
	$list .= '<tr class="row_odd row"><td class="col_even col" colspan="5">' . _r('No feeds installed yet') . '</td></tr>';
}
$list .= '</tbody></table>';
?>
<h3><?php _e('Current Feeds'); ?></h3>
<?php
echo $list;
?>
<div id="changer">
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
		<fieldset id="change">
			<legend><?php _e('Edit Feed'); ?></legend>
			<p class="option">
				<label for="change_name"><?php _e('Name'); ?>:</label>
				<input type="text" name="change_name" id="change_name" />
			</p>
			<p class="option">
				<span class="sidenote"><?php _e('Example'); ?>: http://feeds.feedburner.com/lilina-news</span>
				<label for="change_url"><?php _e('Feed address (URL)'); ?>:</label>
				<input type="text" name="change_url" id="change_url" />
			</p>
			<input type="hidden" name="page" value="feeds" />
			<input type="hidden" name="action" value="change" />
			<p id="changer_id" class="option">
				<label for="change_id"><?php _e('Feed ID'); ?>:</label>
				<input type="text" name="change_id" id="change_id" value="" />
			</p>
			<input type="submit" value="<?php _e('Save Changes'); ?>" />
		</fieldset>
	</form>
</div>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
	<fieldset id="add">
		<legend><?php _e('Add Feed'); ?></legend>
		<p class="option">
			<span class="sidenote"><?php _e('If no name is specified, it will be taken from the feed'); ?></span>
			<label for="add_name"><?php _e('Name'); ?>:</label>
			<input type="text" name="add_name" id="add_name" />
		</p>
		<p class="option">
			<span class="sidenote"><?php _e('Example'); ?>: http://feeds.feedburner.com/lilina-news</span>
			<label for="add_url"><?php _e('Feed address (URL)'); ?>:</label>
			<input type="text" name="add_url" id="add_url" />
		</p>
		<input type="hidden" name="page" value="feeds" />
		<input type="hidden" name="action" value="add" />
		<input type="submit" value="<?php _e('Add Feed'); ?>" />
	</fieldset>
</form>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
	<fieldset id="import">
		<legend><?php _e('Import Feeds'); ?></legend>
		<p class="option">
			<label for="import_url"><?php _e('OPML address (URL)'); ?>:</label>
			<input type="text" name="import_url" id="import_url" />
		</p>
		<input type="hidden" name="page" value="feeds" />
		<input type="hidden" name="action" value="import" />
		<input type="submit" value="<?php _e('Import Feeds from OPML'); ?>" />
	</fieldset>
</form>