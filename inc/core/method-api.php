<?php
/**
 * Application interface for Lilina
 *
 * Mainly a JSON API for templates
 * @package Lilina
 * @subpackage API
 */

class LilinaAPI {
	/**
	 * @var ItemCache
	 */
	protected static $cache = null;

	// General API methods
	public static function init() {
		// Setup headers
		header('Content-Type: application/json');
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		// Setup handler
		$handler = new AjaxHandler();
		$handler->registerMethod('items.get', array('LilinaAPI', 'items_get') );
		$handler->registerMethod('items.getList', array('LilinaAPI', 'items_getList') );
		$handler->registerMethod('feeds.get', array('LilinaAPI', 'feeds_get') );
		$handler->registerMethod('feeds.getList', array('LilinaAPI', 'feeds_getList') );
		$handler->registerMethod('update.single', array('LilinaAPI', 'update_single') );
		Lilina_Plugins::filter_reference('LilinaAPI-register', array(&$handler));

		// Dispatch
		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
		try {
			$output = $handler->handle($action, $_REQUEST);
			echo json_encode($output);
		} catch( Exception $e ) {
			header('HTTP/1.1 500 Internal Server Error');
			echo json_encode( array('error'=>1, 'msg'=>$e->getMessage(), 'code'=>$e->getCode()));
		}
	}

	// Item methods
	public static function items_get($id) {
		// This is to make sure get_the_link() etc. work.
		global $item;
		$item = self::item_convert(Lilina_Items::get_instance()->get($id));
		$item['services'] = Services::get_for_item($item);
		return $item;
	}
	public static function items_getList($start = 0, $limit = null, $conditions = array()) {
		$args = array();
		$start = (int) $start;
		$limit = (int) $limit;
		if ($start !== 0) {
			$args['offset'] = $start;
		}
		if ($limit !== 0) {
			$args['limit'] = $limit;
		}
		if (isset($conditions['feed'])) {
			if (empty($args['where'])) {
				$args['where'] = array();
			}
			$args['where'][] = array('feed_id', '==', $conditions['feed']);
		}
		Lilina_Items::get_instance()->query($args);
		$items = Lilina_Items::get_instance()->get_items();
		$items = array_map(array(__CLASS__, 'item_convert'), $items);
		return $items;
	}

	protected static function item_convert($item) {
		return $item->json_export();
	}

	// Feed methods
	public static function feeds_get($id) {
		return Feeds::get_instance()->get($id);
	}
	public static function feeds_getList() {
		return Feeds::get_instance()->getAll();
	}

	// Updater methods
	public static function update_single($id) {
		require_once(LILINA_INCPATH . '/core/method-update.php');
		$updater = new UpdaterMethod();
		return $updater->process($id);
	}
}

Controller::registerMethod('api', array('LilinaAPI', 'init'));