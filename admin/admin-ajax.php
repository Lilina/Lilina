<?php
/**
 * AJAX Call Processing
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
error_reporting(E_ALL);
require_once('admin.php');
require_once(LILINA_PATH . '/admin/includes/feeds.php');
require_once(LILINA_PATH . '/admin/includes/class-ajaxhandler.php');

//header('Content-Type: application/json');
header('Content-Type: application/javascript');

class AdminAjax {
	/**
	 * Initialise the Ajax interface
	 */
	public static function init() {
		$handler = new AjaxHandler();
		$handler->registerMethod('feeds.add', array('AdminAjax', 'feeds_add') );
		$handler->registerMethod('feeds.change', array('AdminAjax', 'feeds_change') );
		$handler->registerMethod('feeds.remove', array('AdminAjax', 'feeds_remove') );
		$handler->registerMethod('feeds.list', array('AdminAjax', 'feeds_list') );

		$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : null;
		try {
			$output = $handler->handle($method, $_REQUEST);
			echo json_encode($output);
		} catch( Exception $e ) {
			header('HTTP/500 Internal Server Error');
			echo json_encode( array('error'=>1, 'msg'=>$e->getMessage(), 'code'=>$e->getCode()));
		}
	}
	/**
	 * Callback for feeds.add
	 */
	public static function feeds_add($url, $name = '') {
		/** We need some sort of value here 
		if( !isset($params['name']) )
			$params['name'] = '';

		if(empty($params['url']))
			throw new Exception( _r('No URL specified'), Errors::get_code('admin.feeds.no_url') );*/

		add_feed( $url, htmlspecialchars($name) );
		clear_html_cache();
	}
	/**
	 * Callback for feeds.change
	 */
	public static function feeds_change($feed_id, $url, $name = '') {
		$result = change_feed($feed_id, (int) $url, $name);
		clear_html_cache();

		return array();
	}
	/**
	 * Callback for feeds.remove
	 */
	public static function feeds_remove($remove) {
		$success = remove_feed((int) $remove);
		clear_html_cache();
	}
	/**
	 * Callback for feeds.list
	 */
	public static function feeds_list() {
		return array('table' => feed_list_table());
	}
}

AdminAjax::init();