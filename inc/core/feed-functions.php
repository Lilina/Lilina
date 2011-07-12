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
 * Sanitize item's properties
 *
 * Wrapper function for HTML Purifier; sets our settings such as the cache
 * directory and purifies both arrays and strings
 *
 * @since 1.0
 *
 * @param string|array $input HTML to purify
 * @return string|array Purified HTML
 */
function lilina_sanitize_item($item) {
	static $config;

	require_once(LILINA_INCPATH . '/contrib/HTMLPurifier.standalone.php');
	if(!isset($config)) {
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Core.Encoding', get_option('encoding'));
		$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
		$config->set('Cache.SerializerPath', get_option('cachedir'));
		$config = apply_filters('htmlpurifier_config', $config);
	}

	$data = array(
		'title' => $item->title,
		'content' => $item->content,
		'summary' => $item->summary
	);

	$results = HTMLPurifier::instance($config)->purifyArray($data);

	$item->title = $results['title'];
	$item->content = $results['content'];
	$item->summary = $results['summary'];

	return $item;
}