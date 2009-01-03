<?php
/*
Plugin Name: Hot Topic
Plugin URI: http://getlilina.org/docs/plugins:hot_topic
Description: Displays the topic of each item
Author: Ryan McCue
Version: 1.0
Min Version: 1.0
Author URI: http://cubegames.net
License: GPL
*/

function ht_setup() {
	global $ht_stopwords;
	$ht_stopwords = file(dirname(__FILE__) . '/stopwords.txt');
	$ht_stopwords = array_map('strtolower', $ht_stopwords);
	$ht_stopwords = array_map('trim', $ht_stopwords);
	
	global $ht_weirdchars;
	$ht_weirdchars = array(
		'&#8217;'
	);
}
function ht_calculate($content) {
	global $ht_current_topics, $ht_stopwords;
	$ht_current_topics = array();

	$sanitised = ht_destroy_html($content);
	$sanitised = strtolower($sanitised);
	preg_match_all('|([a-zA-Z0-9\pP]*[a-zA-Z0-9])|', $sanitised, $terms);
	$terms = array_map('trim', $terms[0]);
	$terms = array_count_values($terms);

	foreach ($terms as $term => $count) {
		if (in_array($term, $ht_stopwords)) {
			unset($terms[$term]);
		}
	}
	arsort($terms);

	$ht_current_topics = array_slice($terms, 0, 5, true);

	return $content;
}

function ht_display($actions) {
	global $ht_current_topics;
	$actions[] = 'Topics: ' . implode(', ', array_keys($ht_current_topics));
	return $actions;
}

function ht_destroy_html($string) {
	$string = preg_replace('!<(code|pre).*?>.*?</\\1>!is', '', $string);
	$string = strip_tags($string);
	$antismarts = array(
		'&#8212;' => '---',
		' &#8212; ' => ' -- ',
		'&#8211;' => '--',
		'&#8230;' => '...',
		'&#8220;' => "''",
		'&#8217;s' => "'s",
		'&#8221;' => "''"
	);
	$string = strtr($string, $antismarts);
	$string = str_replace(array("\r", "\n"), ' ', $string);
	$string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
	return ht_convert_smart_quotes($string);
}

function ht_convert_smart_quotes($string) {
	$search = array(
		chr(145) => "'",
		chr(146) => "'",
		chr(147) => '"',
		chr(148) => '"',
		chr(151) => '-'
	);

	return strtr($string, $search); 
} 

add_action('init', 'ht_setup');
add_filter('the_content', 'ht_calculate');
add_filter('action_bar', 'ht_display');
?>