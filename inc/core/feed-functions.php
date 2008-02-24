<?php
/**
 * Feed handling functions
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

defined('LILINA') or die('Restricted access');

/**
 * Takes an array of feeds and returns all channels and all items from them
 *
 * Takes an input array and parses it using the SimplePie library. Returns a SimplePie object.
 * @param array $input Input array of user specified feeds
 * @return object SimplePie object with all feed's associated data
 */
function lilina_return_items($input) {
	global $settings, $lilina, $end_errors;
	// Include the SimplePie library
	require_once(LILINA_INCPATH . '/contrib/simplepie/simplepie.inc');
	$items		= array();
	$channels	= '';
	$index		= 0;
	$feed = new SimplePie();
	$feed->set_useragent('Lilina/'. $lilina['core-sys']['version'].'; '.$settings['baseurl']);
	$feed->set_stupidly_fast(true);
	$feed->set_cache_location(LILINA_PATH . '/cache');
	foreach($input['feeds'] as $the_feed)
		$feed_list[] = $the_feed['feed'];
	$feed->set_feed_url($feed_list);
	$feed->init();
	return $feed;
}

/**
 * Parses HTML with HTML Purifier
 *
 * Wrapper function for HTML Purifier; sets our settings such as the cache directory and purifies
 * both arrays and strings
 * @param mixed $val_array Array or string to parse/purify
 * @return mixed Array or string of purified HTML
 */
function lilina_parse_html($val_array){
	require_once(LILINA_INCPATH . '/contrib/HTMLPurifier.standalone.php');
	global $settings;
	$config = HTMLPurifier_Config::createDefault();
	$config->set('Core', 'Encoding', $settings['encoding']); //replace with your encoding
	$config->set('Core', 'XHTML', true); //replace with false if HTML 4.01
	$config->set('HTML', 'Doctype', 'XHTML 1.0 Transitional');
	$config->set('Cache', 'SerializerPath', $settings['cachedir']);
	$purifier = new HTMLPurifier($config);
	if(is_array($val_array)) {
		if(empty($val_array)) return $val_array;
		foreach($val_array as $this_array) {
			if(is_array($this_array)) {
				$purified_array[] = $purifier->purifyArray($this_array);
			}
			else {
				$purified_array[] = $purifier->purify($this_array);
			}
		}
	}
	else {
		$purified_array = $purifier->purify($val_array);
	}
	return apply_filters('parse_html', $purified_array);
}
register_filter('the_title', 'lilina_parse_html');
register_filter('the_content', 'lilina_parse_html');
register_filter('the_summary', 'lilina_parse_html');
register_filter('return_output', 'lilina_parse_html');
?>