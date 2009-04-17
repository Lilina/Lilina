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
require_once(LILINA_PATH . '/admin/includes/feeds.php');

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
			add_feed( $_REQUEST['url'], htmlspecialchars($_REQUEST['name']) );
			save_feeds();
		}
		break;

	case 'change':
		$change_name = ( !empty($_REQUEST['name']) ) ? htmlspecialchars($_REQUEST['name']) : '';
		$change_url  = ( !empty($_REQUEST['url']) ) ? $_REQUEST['url'] : '';
		$change_id   = ( !empty($_REQUEST['feed_id']) ) ? (int) $_REQUEST['feed_id'] : null;
		change_feed($change_id, $change_url, $change_name);
	break;

	case 'remove':
		$remove_id  = ( isset($_REQUEST['remove']) ) ? htmlspecialchars($_REQUEST['remove']) : '';
		remove_feed($remove_id);

	case 'list':
		$extra_messages = feed_list_table();
		break;
}

$output = array(
		'errors' => MessageHandler::get_errors(),
		'messages' => MessageHandler::get_messages()
);

if(!empty($extra_messages))
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