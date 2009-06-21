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
	$table = '';
	if(is_array($feeds) && !empty($feeds)) {
		foreach($feeds as $this_feed) {
			$table .= '
		<tr id="feed-' . $j . '" class="' . (($j % 2) ? 'alt' : '') . '">
			<td class="name-col"><span>' .  stripslashes($this_feed['name']) . '</span></td>
			<td class="url-col"><span>' .  $this_feed['feed'] . '</span></td>
			<!--<td class="cat-col"><span>' . $this_feed['cat'] . '</span></td>-->
			' . apply_filters('admin-feeds-infocol', '', $this_feed, $j) . '
			<!--<td class="change-col"><a href="feeds.php?change=' . $j . '&amp;action=change" class="change_link">' . _r('Change') . '</a></td>-->
			<td class="remove-col"><a href="feeds.php?remove=' . $j . '&amp;action=remove">' . _r('Remove') . '</a></td>
			' . apply_filters('admin-feeds-actioncol', '', $this_feed, $j) . '
		</tr>';
			++$j;
		}
	}
	else {
		$table = '<tr id="nofeeds"><td>' . _r('You don\'t currently have any feeds. Try <a href="#add_form">adding some</a>.') . '</td></tr>';
	}
	return $table;
}

/**
 * 
 * @global array
 */
function get_feeds() {
	global $data;
	if(isset($data['feeds']))
		return $data['feeds'];
	return array(false);
}