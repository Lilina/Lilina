<?php
/**
 * AJAX Call Processing
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
require_once('admin.php');

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

/**
 * feed_list_table() - {@internal Missing Short Description}}
 *
 * {@internal Missing Long Description}}
 */
function feed_list_table() {
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
			<td class="change-col"><a href="<?php echo  $_SERVER['PHP_SELF']; ?>?page=feeds&amp;change=<?php echo  $j; ?>&amp;action=change" class="change_link"><?php _e('Change'); ?></a></td>
			<td class="remove-col"><a href="<?php echo  $_SERVER['PHP_SELF']; ?>?page=feeds&amp;remove=<?php echo  $j; ?>&amp;action=remove"><?php _e('Remove'); ?></a></td>
			<?php do_action('admin-feeds-actioncol', $this_feed, $j); ?>
		</tr>
	<?php
			$alt = empty($alt) ? 'alt' : '';
			++$j;
		}
	}
	else {
	?>
		<tr id="nofeeds"><td><?php _e('No feeds installed yet'); ?></td></tr>
	<?php
	}
}

if(!isset($_REQUEST['action']))
	die('No action specified');

$type = isset( $_REQUEST['type'] ) ? $_REQUEST['type'] : 'json';

switch( $_REQUEST['action'] ) {
	case 'add':
		/** We need some sort of value here */
		if( !isset($_REQUEST['name']) )
			$_REQUEST['name'] = '';

		if(!isset($_REQUEST['url']) || empty($_REQUEST['url']))
			MessageHandler::add_error( _r('No URL specified') );
		else {
			add_feed( htmlspecialchars($_REQUEST['url']), htmlspecialchars($_REQUEST['name']) );
			save_feeds();
		}
		break;

	case 'change':
		global $data;
		if(!isset($_REQUEST['url']) || !isset($_REQUEST['feed_id'])) {
			MessageHandler::add_error( _r('No URL or feed ID specified') );
			break;
		}
		$id = (int) $_REQUEST['feed_id'];
		if( !isset( $data['feeds'][$id] ) ) {
			MessageHandler::add_error( _r('Invalid feed ID specified') );
			break;
		}

		$data['feeds'][ $id ]['feed'] = htmlspecialchars($_REQUEST['url']);
		if(isset($_REQUEST['name']) && !empty($_REQUEST['name'])) {
			$data['feeds'][ $id ]['name'] = htmlspecialchars($_REQUEST['name']);
		}

		$sdata	= base64_encode(serialize($data)) ;
		$fp		= fopen(get_option('files', 'feeds'),'w');
		if(!$fp) {
			MessageHandler::add_error(sprintf(_r('An error occurred when saving to %s and your data may not have been saved'), get_option('files', 'feeds')));
			break;
		}
		fputs($fp,$sdata);
		fclose($fp);

		MessageHandler::add(sprintf(_r('Changed "%s" (#%d)'), $data['feeds'][ $id ]['name'], $id));
	break;

	case 'remove':
		global $data;
		$remove_id	= (int) isset($_REQUEST['remove']) ? $_REQUEST['remove'] : 0;
		$removed = $data['feeds'][$remove_id];
		unset($data['feeds'][$remove_id]);
		$data['feeds'] = array_values($data['feeds']);

		$sdata	= base64_encode(serialize($data));
		$fp		= fopen(get_option('files', 'feeds'),'w');
		if(!$fp) {
			MessageHandler::add_error(sprintf(_r('An error occurred when saving to %s and your data may not have been saved'), get_option('files', 'feeds')));
			break;
		}
		fputs($fp, $sdata);
		fclose($fp);

		MessageHandler::add(
			sprintf(
				_r('Removed feed &mdash; <a href="%s">Undo</a>?'),
				'feeds.php?action=add&amp;add_name=' . urlencode($removed['name']) . '&amp;add_url=' . urlencode($removed['feed'])
			)
		);
		break;

	case 'list':
		$extra_messages = feed_list_table();
		break;
}

$output = array(
		'errors' => MessageHandler::get_errors(),
		'messages' => MessageHandler::get_messages()
);
$output[] = $extra_messages;

/** Remove empty entries, such as 'errors' or 'messages' */
foreach($output as $key => $entry) {
	if(empty($entry))
		unset($output[$key]);
}

/** Allow for different return types */
switch($type) {
	case 'raw':
		implode("\n", $output);
		break;

	default:
		echo json_encode($output);
}

/** End here, just for fun */
die();