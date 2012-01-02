<?php
/**
 * AJAX Call Processing
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

// Fool the authentication so we can handle it ourselves
define('LILINA_LOGIN', true);

require_once('admin.php');
class_exists('Lilina_HTTP');
require_once(LILINA_PATH . '/admin/includes/feeds.php');

//header('Content-Type: application/javascript');
header('Content-Type: application/json');
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
		$feed = Lilina_Feed::create($url, $name);
		$result = Lilina_Feeds::get_instance()->insert($feed);
		return array(
			'success' => 1,
			'msg' => sprintf(_r('Added feed "%1$s"'), $feed->name),
			'data' => $feed
		);
	}
	/**
	 * Callback for feeds.change
	 */
	public static function feeds_change($feed_id, $url = '', $name = '') {
		$feed = Lilina_Feeds::get_instance()->get($feed_id);

		if (!empty($url)) {
			$feed->feed = $url;
		}

		if (!empty($name)) {
			$feed->name = $name;
		}

		Lilina_Feeds::get_instance()->update($feed);

		return array(
			'success' => 1,
			'msg' => sprintf(_r('Changed "%s"'), $feed->name),
			'data' => $feed
		);
	}
	/**
	 * Callback for feeds.remove
	 */
	public static function feeds_remove($feed_id) {
		$feed = Lilina_Feeds::get_instance()->get($feed_id);
		Lilina_Feeds::get_instance()->delete($feed);

		return array(
			'success' => 1,
			'msg' => sprintf(
				_r('Removed "%1$s" &mdash; <a href="%2$s">Undo</a>?'),
				$feed->name,
				'feeds.php?action=add&amp;add_name=' . urlencode($feed->name) . '&amp;add_url=' . urlencode($feed->feed)
			)
		);
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
	public static function feeds_get($start = 0, $limit = 0) {
		$args = array();
		$start = (int) $start;
		$limit = (int) $limit;
		if ($start !== 0) {
			$args['offset'] = $start;
		}
		if ($limit !== 0) {
			$args['limit'] = $limit;
		}

		Lilina_Feeds::get_instance()->query($args);

		return Lilina_Feeds::get_instance()->get_items();
	}
}

AdminAjax::init();