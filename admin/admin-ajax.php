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
// Fool the authentication so we can handle it ourselves
define('LILINA_LOGIN', true);

require_once('admin.php');
require_once(LILINA_PATH . '/admin/includes/feeds.php');
require_once(LILINA_PATH . '/admin/includes/class-ajaxhandler.php');

//header('Content-Type: application/json');
header('Content-Type: application/javascript');
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if(defined('LILINA_AUTH_ERROR')) {
	header('HTTP/1.1 401 Unauthorized');
	echo json_encode( array('error' => 1, 'msg' => _r('You are not currently logged in'), 'code' => Errors::get_code('auth.none')) );
	die();
}
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
		$handler->registerMethod('feeds.get', array('AdminAjax', 'feeds_get') );

		$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : null;
		try {
			$output = $handler->handle($method, $_REQUEST);
			echo json_encode($output);
		} catch( Exception $e ) {
			header('HTTP/1.1 500 Internal Server Error');
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

		$result = add_feed( $url, htmlspecialchars($name) );
		clear_html_cache();
		return array('success' => 1, 'msg' => $result);
	}
	/**
	 * Callback for feeds.change
	 */
	public static function feeds_change($feed_id, $url, $name = '') {
		$result = change_feed((int) $feed_id, $url, $name);
		clear_html_cache();

		return array('success' => 1, 'msg' => $result, 'url' => $url, 'name' => $name);
	}
	/**
	 * Callback for feeds.remove
	 */
	public static function feeds_remove($feed_id) {
		$success = remove_feed((int) $feed_id);
		clear_html_cache();
		return array('success' => 1, 'msg' => $success);
	}
	/**
	 * Callback for feeds.list
	 */
	public static function feeds_list() {
		return array('table' => feed_list_table());
	}
	/**
	 * Callback for feeds.get
	 */
	public static function feeds_get() {
		return get_feeds();
	}
}

AdminAjax::init();