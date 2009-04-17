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
function lilina_return_items($input) {
	global $lilina;

	require_once(LILINA_INCPATH . '/contrib/simplepie/simplepie.inc');

	$feed = new SimplePie();
	$feed->set_useragent('Lilina/'. $lilina['core-sys']['version'].'; ('.get_option('baseurl').'; http://getlilina.org/; Allow Like Gecko) SimplePie/' . SIMPLEPIE_BUILD);
	/** This disables sorting too, we handle that ourselves later */
	$feed->set_stupidly_fast(true);
	$feed->set_cache_location(get_option('cachedir'));
	$feed->set_favicon_handler(get_option('baseurl') . 'lilina-favicon.php');
	$feed = apply_filters('simplepie-config', $feed);

	foreach($input['feeds'] as $the_feed)
		$feed_list[] = $the_feed['feed'];

	$feed->set_feed_url($feed_list);
	$feed->init();

	/** We need this so we have something to work with. */
	$feed->get_items();

	if(!isset($feed->data['ordered_items'])) {
		$feed->data['ordered_items'] = $feed->data['items'];
	}
	/** We disable sorting previously; so we force it here */
	usort($feed->data['ordered_items'], array(&$feed, 'sort_items'));
	usort($feed->data['items'], array(&$feed, 'sort_items'));
	//var_dump($feed);
	return apply_filters('return_items', $feed);
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
		$config->set('Core', 'Encoding', get_option('encoding'));
		$config->set('HTML', 'XHTML', true);
		$config->set('HTML', 'Doctype', 'XHTML 1.0 Transitional');
		$config->set('Cache', 'SerializerPath', get_option('cachedir'));
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
 * @return bool True if succeeded, false if failed
 */
function add_feed($url, $name = '', $cat = 'default') {
	global $data;
	/** Fix users' kludges; They'll thank us for it */
	if(empty($url)) {
		if(function_exists('_r'))
			MessageHandler::add_error(_r("Couldn't add feed: No feed URL supplied"));
		else
			MessageHandler::add_error("Couldn't add feed: No feed URL supplied");
		return false;
	}

	require_once(LILINA_INCPATH . '/contrib/simplepie/simplepie.inc');
	$feed_info = new SimplePie();
	$feed_info->set_useragent('Lilina/'. LILINA_CORE_VERSION . '; (' . get_option('baseurl') . '; http://getlilina.org/; Allow Like Gecko) SimplePie/' . SIMPLEPIE_BUILD);
	$feed_info->set_stupidly_fast(true);
	$feed_info->enable_cache(false);
	$feed_info->set_feed_url($url);
	$feed_info->init();
	$feed_error = $feed_info->error();
	$feed_url = $feed_info->subscribe_url();

	if(!empty($feed_error)) {
		//No feeds autodiscovered;
		if(function_exists('_r'))
			MessageHandler::add_error(sprintf(_r( "Couldn't add feed: %s is not a valid URL or the server could not be accessed. Additionally, no feeds could be found by autodiscovery." ), $url ));
		else
			MessageHandler::add_error("Couldn't add feed: $url is not a valid URL or the server could not be accessed. Additionally, no feeds could be found by autodiscovery.");

		return false;
	}

	if(empty($name)) {
		//Get it from the feed
		$name = $feed_info->get_title();
	}

	$data['feeds'][] = array(
		'feed'	=> $feed_url,
		'url'	=> $feed_info->get_link(),
		'name'	=> $name,
		'cat'	=> $cat,
	);
	if(function_exists('_r'))
		MessageHandler::add( sprintf( _r('Added feed "%1$s"'), $name ) );
	else
		MessageHandler::add( "Added feed \"$name\"");

	save_feeds();
	return true;
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
	if(empty($id) || empty($url)) {
		MessageHandler::add_error(_r('No URL or feed ID specified'));
		return false;
	}

	global $data;
	if(empty($data['feeds'][$id])) {
		MessageHandler::add_error(_r('Feed does not exist'));
	}
	$feed = array('feed' => $url);
	if(!empty($category)) {
		$feed['cat'] = $category;
	}
	if(!empty($name)) {
		$feed['name'] = $name;
	}
	save_feeds();
	MessageHandler::add(sprintf(_r('Changed "%s" (#%d)'), $change_name, $change_id));
	return true;
}

/**
 * Remove a feed
 *
 * @param int $id ID of the feed to remove
 * @return bool
 */
function remove_feed($id) {
	global $data;

	if(!isset($data['feeds'][$id])) {
		MessageHandler::add_error(_r('Feed does not exist'));
		return false;
	}

	//Make a copy for later.
	$removed = $data['feeds'][$id];
	unset($data['feeds'][$id]);
	//Reorder array
	$data['feeds'] = array_values($data['feeds']);

	save_feeds();
	MessageHandler::add(sprintf(
		_r('Removed feed &mdash; <a href="%s">Undo</a>?'),
		'feeds.php?action=add&amp;add_name=' . urlencode($removed['name']) . '&amp;add_url=' . urlencode($removed['feed'])
	));
	return true;
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
 * @return bool True if feeds were successfully saved, false otherwise
 */
function save_feeds() {
	global $data;
	$file = new DataHandler(LILINA_CONTENT_DIR . '/system/config/');
	return $file->save('feeds.data', base64_encode(serialize($data)));
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
