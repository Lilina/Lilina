<?php
/**
 * Feeds page
 * @todo Move to admin/feeds.php
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
defined('LILINA_PATH') or die('Restricted access');
require_once(LILINA_INCPATH . '/core/category-functions.php');

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

$action = (isset($_GET['action'])? $_GET['action'] : '');

/** Make sure we're actually adding */
if($action == 'add') {
	/** We need some sort of value here */
	if( !isset($_GET['add_name']) )
		$_GET['add_name'] = '';

	if(!isset($_GET['add_url']))
		add_notice(_r('No URL specified'));
	else
		add_feed($_GET['add_url'], $_GET['add_name']);
}
elseif($action == 'import') {
	if(!isset($_GET['import_url']))
		add_notice(_r('No URL specified to import OPML from'));
	else
		import_opml($_GET['import_url']);
}


admin_header();
?>
<h2><?php _e('Feeds'); ?></h2>
<h3><?php _e('Current Feeds'); ?></h3>
<table id="feeds_list">
	<thead>
		<tr>
		<th><?php _e('Feed ID'); ?></th>
		<th><?php _e('Feed Name'); ?></th>
		<th><?php _e('URL'); ?></th>
		<th><?php _e('Category'); ?></th>
		<?php do_action('admin-feeds-infocol-description', $this_feed, $j); ?>
		<th class="change-col"><?php _e('Change Feed'); ?></th>
		<th class="remove-col"><?php _e('Remove Feed'); ?></th>
		<?php do_action('admin-feeds-actioncol-description', $this_feed, $j); ?>
		</tr>
	</thead>
	<tbody>
<?php
//Defined in admin panel
$feeds			= get_feed_list();
$j	= 0;
if(is_array($feeds) && !empty($feeds)) {
	foreach($feeds as $this_feed) {
?>
	<tr class="<?php echo $alt; ?>">
		<td class="id-col"><?php echo $j; ?></td>
		<td class="name-col"><?php echo stripslashes($this_feed['name']); ?></td>
		<td class="url-col"><?php echo $this_feed['feed']; ?></td>
		<td class="cat-col"><?php echo $this_feed['cat']; ?></td>
		<?php do_action('admin-feeds-infocol', $this_feed, $j); ?>
		<td class="change-col"><a href="<?php echo  $_SERVER['PHP_SELF']; ?>?page=feeds&amp;change=<?php echo  $j; ?>&amp;action=change" class="change_link">Change</a></td>
		<td class="remove-col"><a href="<?php echo  $_SERVER['PHP_SELF']; ?>?page=feeds&amp;remove=<?php echo  $j; ?>&amp;action=remove">Remove</a></td>
		<?php do_action('admin-feeds-actioncol', $this_feed, $j); ?>
	</tr>
<?php
		$alt = empty($alt) ? 'alt' : '';
		++$j;
	}
}
else {
	$list .= '<tr class="row_odd row"><td class="col_even col" colspan="5">' . _r('No feeds installed yet') . '</td></tr>';
}
?>
	</tbody>
</table>
<div id="changer">
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
		<fieldset id="change">
			<legend><?php _e('Edit Feed'); ?></legend>
			<p class="option">
				<label for="change_name"><?php _e('Name'); ?>:</label>
				<input type="text" name="change_name" id="change_name" />
			</p>
			<p class="option">
				<span class="sidenote"><?php _e('Example'); ?>: http://feeds.feedburner.com/lilina-news, http://getlilina.org</span>
				<label for="change_url"><?php _e('Feed address (URL)'); ?>:</label>
				<input type="text" name="change_url" id="change_url" />
			</p>
			<p class="option">
				<label for="change_cat"><?php _e('Category'); ?>:</label>
				<select name="change_cat" id="change_cat">
				<?php
				foreach(get_categories() as $category) {
					echo "<option value='{$category['id']}'>{$category['name']}</option>";
				}
				?>
				</select>
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
<?php
admin_footer();
?>