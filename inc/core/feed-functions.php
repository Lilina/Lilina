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
		$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
		$config->set('Cache.SerializerPath', get_option('cachedir'));
		$config = apply_filters('htmlpurifier_config', $config);
		$purifier = new HTMLPurifier($config);
	}

	return apply_filters('parse_html', $purifier->purify($input));
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