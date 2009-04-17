<?php
/**
 * Feed administration helpers
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * feed_list_table() - {@internal Missing Short Description}}
 *
 * {@internal Missing Long Description}}
 */
function feed_list_table() {
	//Defined in admin panel
	$feeds			= get_feeds();
	$j	= 0;
	if(is_array($feeds) && !empty($feeds)) {
		foreach($feeds as $this_feed) {
	?>
		<tr id="feed-<?php echo $j ?>" class="<?php echo ($j % 2) ? 'alt' : ''; ?>">
			<td class="name-col"><?php echo stripslashes($this_feed['name']); ?></td>
			<td class="url-col"><?php echo $this_feed['feed']; ?></td>
			<td class="cat-col"><?php echo $this_feed['cat']; ?></td>
			<?php do_action('admin-feeds-infocol', $this_feed, $j); ?>
			<td class="change-col"><a href="feeds.php?change=<?php echo $j; ?>&amp;action=change" class="change_link"><?php _e('Change'); ?></a></td>
			<td class="remove-col"><a href="feeds.php?remove=<?php echo $j; ?>&amp;action=remove"><?php _e('Remove'); ?></a></td>
			<?php do_action('admin-feeds-actioncol', $this_feed, $j); ?>
		</tr>
	<?php
			++$j;
		}
	}
	else {
	?>
		<tr id="nofeeds"><td><?php _e('You don\'t currently have any feeds. Try <a href="#add_form">adding some</a>.'); ?></td></tr>
	<?php
	}
}

/**
 * 
 * @global array
 */
function get_feeds() {
	global $data;
	if(isset($data['feeds']))
		return $data['feeds'];
	return false;
}