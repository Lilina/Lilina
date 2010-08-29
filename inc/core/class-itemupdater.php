<?php
/**
 * This class handles the updating of items from feeds
 *
 * @package Lilina
 */

/**
 * This class handles the updating of items from feeds.
 *
 * Contains both command line and browser interfaces.
 * @package Lilina
 */
class ItemUpdater {
	protected static $feeds = array();
	public static $fatal = true;

	protected static $errors = array();

	public static function set_feeds($feeds) {
		self::$feeds = $feeds;
	}

	/**
	 * Process through the feeds and add the new items to the database
	 */
	public static function process() {
		$reporting = error_reporting();
		error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR);

		require_once(LILINA_INCPATH . '/contrib/simplepie/simplepie.inc');
		$updated = false;
		$return = array();

		foreach(self::$feeds as $feed) {
			$result = self::process_single($feed);
			if($result > 0)
				$updated = true;
			$return[ $feed['id'] ] = $result;
		}

		Items::get_instance()->sort_all();

		if($updated) {
			Items::get_instance()->save_cache();
			update_option('last_updated', time());
		}

		error_reporting($reporting);
		return $return;
	}

	/**
	 * Process a single feed
	 *
	 * @param array $feed Feed information (required elements are 'name' for error reporting, 'feed' for the feed URL and 'id' for the feed's unique internal ID)
	 * @return int Number of items added
	 */
	public static function process_single($feed) {
		do_action('iu-feed-start', $feed);

		$sp = &self::load_feed($feed);
		if($error = $sp->error()) {
			self::log(sprintf(_r('An error occurred with "%2$s": %1$s'), $error, $feed['name']), Errors::get_code('api.itemupdater.itemerror'));
			do_action('iu-feed-finish', $feed);
			return -1;
		}

		$count = 0;
		$items = $sp->get_items();
		foreach($items as $item) {
			$new_item = self::normalise($item, $feed['id']);
			$new_item = apply_filters('item_data_precache', $new_item, $feed);
			if(Items::get_instance()->check_item($new_item)) {
				$count++;
				do_action('iu-item-add', $new_item, $feed);
			}
			else {
				do_action('iu-item-noadd', $new_item, $feed);
			}
		}
		$sp->__destruct();
		unset($sp);

		do_action('iu-feed-finish', $feed);
		return $count;
	}

	/**
	 * Load and process a feed using SimplePie
	 *
	 * @param string $feed Feed detail array, as returned by Feeds::get()
	 * @return SimplePie
	 */
	public static function &load_feed($feed) {
		// This loads the useragent
		class_exists('HTTPRequest');
		global $lilina;

		$sp = new SimplePie();
		$sp->set_useragent(LILINA_USERAGENT . ' SimplePie/' . SIMPLEPIE_BUILD);
		$sp->set_stupidly_fast(true);
		$sp->set_cache_location(get_option('cachedir'));
		//$sp->set_cache_duration(0);
		$sp->set_favicon_handler(get_option('baseurl') . 'lilina-favicon.php');
		$sp = apply_filters('simplepie-config', $sp);

		$sp->set_feed_url($feed['feed']);
		$sp->init();

		/** We need this so we have something to work with. */
		$sp->get_items();

		if(!isset($sp->data['ordered_items'])) {
			$sp->data['ordered_items'] = $sp->data['items'];
		}

		/** Let's force sorting */
		usort($sp->data['ordered_items'], array(&$sp, 'sort_items'));
		usort($sp->data['items'], array(&$sp, 'sort_items'));

		do_action_ref_array('iu-load-feed', array(&$sp, $feed));
		return $sp;
	}

	/**
	 * Normalise a SimplePie_Item into a stdClass
	 *
	 * Converts a SimplePie_Item into a new-style stdClass
	 */
	public function normalise($item, $feed = '') {
		if($enclosure = $item->get_enclosure()) {
			$enclosure_data = (object) array(
				'type' => $enclosure->get_real_type(),
				'length' => $enclosure->get_length()
			);
			$enclosure = $enclosure->get_link();
		}
		else {
			// SimplePie_Item::get_enclosure() returns null, so we need to change this to false
			$enclosure = false;
			$enclosure_data = false;
		}
		if($author = $item->get_author()) {
			$author = array(
				'name' => $item->get_author()->get_name(),
				'url' => $item->get_author()->get_link()
			);
		}
		else {
			$author = array(
				'name' => false,
				'url' => false
			);
		}
		$new_item = (object) array(
			'hash'      => $item->get_id(true),
			'timestamp' => $item->get_date('U'),
			'title'     => $item->get_title(),
			'content'   => $item->get_content(),
			'summary'   => $item->get_description(),
			'permalink' => $item->get_permalink(),
			'metadata'  => (object) array(
				'enclosure' => $enclosure,
				'enclosure_data' => $enclosure_data
			),
			'author'    => (object) $author,
			'feed'      => $item->get_feed()->get_link()
		);
		if(!empty($feed))
			$new_item->feed_id = $feed;
		return apply_filters('item_data', $new_item, $item);
	}

	/**
	 * Log errors
	 */
	public static function log($detail, $code) {
		if(self::$fatal) {
			throw new Exception($detail, $code);
		}
		else {
			ItemUpdater::$errors[] = array(
				'code' => $code,
				'msg' => $detail
			);
		}
	}
}