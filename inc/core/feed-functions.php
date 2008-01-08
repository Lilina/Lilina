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
 * @todo Document
 * @todo This is kludgy with SimplePie. Move to separate functions in skin.php
 */
function lilina_return_output($all_items) {
	return;
	global $showtime, $settings;
	$out	= array();
	$index	= 0;
	//usort($all_items, 'date_cmp');
	foreach($all_items->get_items() as $item) {
		$feed = $item->get_feed();
		var_dump($item);
		//Only display the feeds from the chosen times
		if ( ($showtime>-1) && (time() - $item['date_timestamp'] > $showtime) ) {
			break;
		}
		$out[$index]['summary']	= $item->get_description();
		$out[$index]['content']	= $item->get_content();
		/** Need to convert this to get_local_date() */
		$out[$index]['date'] = $item->get_date('l d F, Y');
		$out[$index]['old_date']	= ($index != 0) ? $out[$index-1]['date'] : '';
		$out[$index]['time']		= date('H:i', $item['date_timestamp'] ) ;
		$out[$index]['timestamp']	= $item['date_timestamp'];
		$out[$index]['channel_link']= $item['channel_url']; 
		$out[$index]['link']		= (!isset($item['link']) || empty($item['link'])) ? $item['guid'] : $item['link'];
		$out[$index]['guid']		= (!isset($item['guid']) || empty($item['guid'])) ? $item['link'] : $item['guid'];
		$out[$index]['old_channel']	= (isset($channel_url_old)) ? $channel_url_old : '' ;
		$out[$index]['id']			= md5($out[$index]['link'] . $out[$index]['channel_link']);
		$out[$index]['icon']		= (isset($item['favicon'])) ? $item['favicon'] : '' ;
		$out[$index]['title']		= (!isset($item['title']) || empty($item['title'])) ? _r('(No title)') : $item['title'];
		$out[$index]['channel_title']	= $item['channel_title'];
		//First enclosure listed is the one displayed
		if(isset($item['enclosures'])){
			if(is_array($item['enclosures'])) {
				$out[$index]['enclosures']	= $item['enclosures'];
			}
			elseif(!empty($item['enclosures'])) {
				//There, but empty... What should we do?
				$out[$index]['enclosures']	= '';
			}
			else {
				$out[$index]['enclosures']	= '';
			}
		}
		$channel_url_old	= $out[$index]['channel_link'];
		++$index;
	}
	return apply_filters('return_output', $out);
}

/**
 * Retrieve available feeds for a given page
 *
 * Originally by Keith Devens; includes improvements by "Cristian"
 * @author Keith Devens
 * @link http://keithdevens.com/weblog/archive/2002/Jun/03/RSSAuto-DiscoveryPHP
 * @link http://keithdevens.com/weblog/archive/2002/Jun/03/RSSAuto-DiscoveryPHP#comment9695
 */
function lilina_get_rss($location) {
    if(!$location) {
        return false;
    }
	$html = @file_get_contents($location);
	if(!$html) {
		return false;
	}
	//search through the HTML, save all <link> tags
	// and store each link's attributes in an associative array
	preg_match_all('/<link\s+(.*?)\s*\/?>/si', $html, $matches);
	$links = $matches[1];
	$final_links = array();
	$link_count = count($links);
	for($n=0; $n<$link_count; $n++){
		$attributes = preg_split('/\s+/s', $links[$n]);
		foreach($attributes as $attribute){
			$att = preg_split('/\s*=\s*/s', $attribute, 2);
			if(isset($att[1])){
				$att[1] = preg_replace('/([\'"]?)(.*)\1/', '$2', $att[1]);
				$final_link[strtolower($att[0])] = $att[1];
			}
		}
		$final_links[$n] = str_replace('"', '', $final_link);
	}
	//now figure out which one points to the RSS file
	for($n=0; $n<$link_count; $n++){
		if(strtolower($final_links[$n]['rel']) == 'alternate'){
			if(strtolower($final_links[$n]['type']) == 'application/rss+xml' || strtolower($final_links[$n]['type']) == 'application/atom+xml'){
				$href = $final_links[$n]['href'];
			}
			if(!isset($href) && strtolower($final_links[$n]['type']) == 'text/xml'){
				//kludge to make the first version of this still work
				$href = $final_links[$n]['href'];
			}
			if(!empty($href)){
				if(strstr($href, "http://") !== false) { //if it's absolute
					$full_url[] = $href;
				}
				else {
					//otherwise, 'absolutize' it
					$url_parts = parse_url($location);
					//only made it work for http:// links. Any problem with this?
					$full_url[] = "http://$url_parts[host]";
					if(isset($url_parts['port'])){
						$full_url[count($full_url)-1] .= ":$url_parts[port]";
					}
					if($href[0] != '/'){ //it's a relative link on the domain
						$full_url[count($full_url)-1] .= dirname($url_parts['path']);
						if(substr($full_url[count($full_url)-1], -1) != '/'){
							//if the last character isn't a '/', add it
							$full_url[count($full_url)-1] .= '/';
						}
					}
					$full_url[count($full_url)-1] .= $href;
				}
				//return $full_url;
			}
		}
	}
	if (isset($full_url)) {
		return apply_filters('get_rss', $full_url);
	}
	else {
		return false;
	}
}

/**
 * Takes an array of feeds and returns all channels and all items from them
 *
 * Takes an input array and parses it using the Magpie library. Returns channel info such as
 * the name, link, icon and feed url. Takes the items returned by Magpie
 * and adds the icon, fixes the timestamp and adds the channel information.
 * @param array $input Input array of user specified feeds
 * @return array All channels and all items
 */
function lilina_return_items($input) {
	global $settings, $lilina, $end_errors;
	// Include the SimplePie library
	require_once(LILINA_INCPATH . '/contrib/simplepie/simplepie.inc');
	$items		= array();
	$channels	= '';
	$index		= 0;
	// print_r($input);
	// die();
	$feed = new SimplePie();
	$feed->set_useragent('Lilina/'. $lilina['core-sys']['version'].'; '.$settings['baseurl']);
	$feed->set_stupidly_fast(true);
	$feed->set_cache_location(LILINA_PATH . '/cache');
	foreach($input['feeds'] as $the_feed)
		$feed_list[] = $the_feed['feed'];
	$feed->set_feed_url($feed_list);
	$feed->init();
	return $feed;
	/*
	foreach($feeds as $feed) {
		$rss	= fetch_rss( $feed['feed'] );
		if (!$rss){
			$end_errors	.= '<br />Could not fetch feed: ' . $feed['feed'] . '<br /> Magpie returned: ' . magpie_error();
			continue;
		}
		//Get the icon to display
		if(!isset($rss->channel['link']) || empty($rss->channel['link'])) {
			//We really need better code here
			$rss->channel['link'] = $feed['feed'];
		}
		$channels[$index]['icon']	= channel_favicon( $rss->channel['link'] );
		$channels[$index]['link']	= $rss->channel['link'];
		$channels[$index]['name']	= (empty($feed['name'])) ? $rss->channel['title'] :  $feed['name'];
		$channels[$index]['feed']	= $feed['feed'];
		
		if($settings['feeds']['items']) {
			//User has specified limit, limit the items
			$limited_items = array_slice($rss->items, 0, $settings['feeds']['items']);			
		}
		else {
			//No limit, don't bother slicing
			$limited_items	= $rss->items;
		}
		foreach($limited_items as $item){
			if(isset($feed['name']) && !empty($feed['name'])){
				$item['channel_title']	= $feed['name'];
			}
			else {
				$item['channel_title']	= $channels[$index]['name'];
			}
			if(!isset($item['title']) || empty($item['title'])){
				$item['title']			= _r('(No title)');
			}
			if(!isset($item['link']) || empty($item['link'])){
				$item['link']			= $channels[$index]['link'];
			}
			$item['channel_url']		= $channels[$index]['link'];
			$item['favicon']			= $channels[$index]['icon'];
			if (!isset($item['date_timestamp']) || empty($item['date_timestamp'])) {
				//No date set
				if(isset($item['pubdate']) && !empty($item['pubdate'])) {
					//It's set in a different way by the feed, lets use it
					$item['date_timestamp'] = strtotime($item['pubdate']);
					if(!isset($item['date_timestamp']) || empty($item['date_timestamp']) || $item['date_timestamp'] <= 0){
						//OK, we lied, that doesn't work either				
						$item['date_timestamp']	= create_time($item['title'] . $item['link']);
					}
				}
				elseif(isset($item['dc']['date']) && !empty($item['dc']['date'])) {
					//Support for Dublin Core
					$item['date_timestamp']	= parse_w3cdtf($item['dc']['date']);
				}
				elseif(isset($item['published']) && !empty($item['published'])) {
					$item['date_timestamp'] = strtotime($item['published']);
				}
				elseif(isset($item['updated']) && !empty($item['updated'])) {
					$item['date_timestamp'] = strtotime($item['updated']);
				}	
				else {
					//This feed doesn't like us
					$item['date_timestamp']	= create_time($item['title'] . $item['link']);
				}
			}
			$item['date_timestamp']	+= $settings['offset'] * 60 * 60;
			$items[] = $item ;
		}
		++$index;
	}
	return apply_filters('return_items', array($channels, $items));*/
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
register_filter('return_output', 'lilina_parse_html', 1);
?>