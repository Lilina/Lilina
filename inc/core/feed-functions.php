<?php
/**
 * Feed handling functions
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

defined('LILINA_PATH') or die('Restricted access');

/**
 * Generates a SimplePie object from a list of feeds
 *
 * Takes an input array and parses it using the SimplePie library. Returns a SimplePie object.
 * @param array $input Input array of user specified feeds
 * @return object SimplePie object with all feed's associated data
 */
function lilina_return_items($input, $conditions = array()) {
	foreach($input['feeds'] as $the_feed)
		$feed_list[] = $the_feed['feed'];
	$itemcache = new ItemCache();
	$itemcache->set_feeds($feed_list);
	$itemcache->init();
	$itemcache->set_conditions(array('time' => (time() - 86400)));
	$itemcache->filter();
	return apply_filters('return_items', $itemcache);
}

/**
 * Sanitize HTML code
 *
 * Wrapper function for HTML Purifier; sets our settings such as the cache
 * directory and purifies both arrays and strings
 *
 * @since 1.0
 * @todo Make really recursive instead of faux recursing
 *
 * @param string|array $input HTML to purify
 * @return string|array Purified HTML
 */
function lilina_parse_html($input) {
	static $purifier;
	if(empty($input))
		return $input;

	require_once(LILINA_INCPATH . '/contrib/HTMLPurifier.standalone.php');
	if(!isset($purifier) || !is_a($purifier, 'HTMLPurifier')) {
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Core.Encoding', get_option('encoding'));
		$config->set('HTML.XHTML', true);
		$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
		$config->set('Cache.SerializerPath', get_option('cachedir'));
		$config = apply_filters('htmlpurifier_config', $config);
		$purifier = new HTMLPurifier($config);
	}

	if(!is_array($input)) {
		return apply_filters('parse_html', $purifier->purify($input));
	}
	
	array_walk_recursive($input, 'lilina_parse_html');

	return apply_filters('parse_html', $input);
}

/**
 * Sanitize item's properties
 *
 * Wrapper function for HTML Purifier; sets our settings such as the cache
 * directory and purifies both arrays and strings
 *
 * @since 1.0
 * @todo Make really recursive instead of faux recursing
 *
 * @param string|array $input HTML to purify
 * @return string|array Purified HTML
 */
function lilina_sanitize_item($item) {
	$item->title = lilina_parse_html($item->title);
	$item->content = lilina_parse_html($item->content);
	$item->summary = lilina_parse_html($item->summary);
	return apply_filters('sanitize_item', $item);
}

/**
 * Add a new feed to the database
 *
 * Adds the specified feed name and URL to the global <tt>$data</tt> array. If no name is set
 * by the user, it fetches one from the feed. If the URL specified is a HTML page and not a
 * feed, it lets SimplePie do autodiscovery and uses the XML url returned.
 *
 * @since 1.0
 * @uses $data Contains all feeds, this is what we add the new feed to
 *
 * @param string $url URL to feed or website (if autodiscovering)
 * @param string $name Title/Name of feed
 * @param string $cat Category to add feed to
 * @param bool $return If true, return the new feed's details. Otherwise, use the global $data array
 * @return bool True if succeeded, false if failed
 */
function add_feed($url, $name = '', $cat = 'default', $return = false) {
	return Feeds::get_instance()->add($url, $name, $cat);
}

/**
 * Change a feed's properties
 *
 * @param int $id ID of the feed to change
 * @param string $url Feed URL
 * @param string $name Name of the feed (optional)
 * @param string $category Category of the feed (optional)
 * @return bool
 */
function change_feed($id, $url, $name = '', $category = '') {
	throw new Exception("This isn't handled yet, because I'm lazy. Please fix me. If you see this, report a bug and I'll fix it.");
}

/**
 * Remove a feed
 *
 * @param int $id ID of the feed to remove
 * @return bool
 */
function remove_feed($id) {
	return Feeds::get_instance()->delete($id);
}

/**
 * Load feeds into global $data
 *
 * @uses $data
 * @return array
 */
function load_feeds() {
	global $data;

	$file = new DataHandler(LILINA_CONTENT_DIR . '/system/config/');
	$data = $file->load('feeds.data');
	if($data !== null)
		$data = unserialize(base64_decode($data));
	else
		$data = array();

	return $data;
}

/**
 * Save feeds to a config file
 *
 * Serializes, then base 64 encodes.
 * @param array $feeds Data to save to config. If not specified, taken from global $data variable.
 * @return bool True if feeds were successfully saved, false otherwise
 */
function save_feeds($feeds = null) {
	if(empty($feeds)) {
		global $data;
		$feeds = $data;
	}
	$file = new DataHandler(LILINA_CONTENT_DIR . '/system/config/');
	return $file->save('feeds.data', base64_encode(serialize($feeds)));
}

/**
 * Retrieve the custom name of a feed based on a URL
 *
 * @param string $name Default name to return if URL is not found
 * @param string $url URL to lookup
 * @return string Name of the feed
 */
function get_feed_name($name, $url) {
	global $data;
	if($feed = feed_exists($url)) {
		$name = $feed['name'];
	}
	return $name;
}

/**
 * Check for the existence of a feed based on a URL
 *
 * Checks whether the supplied feed is in the feed list
 * @param string $url URL to lookup
 * @return array|bool Returns the feed array if found, false otherwise
 */
function feed_exists($url) {
	global $data;
	foreach($data['feeds'] as $feed) {
		if($feed['url'] === $url || strpos($feed['url'], $url)) {
			return $feed;
		}
	}
	return false;
}
?>
