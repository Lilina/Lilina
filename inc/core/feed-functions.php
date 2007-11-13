<?php
/**
 * Feed handling functions
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

defined('LILINA') or die('Restricted access');

function lilina_return_output($all_items) {
	global $showtime, $settings;
	$out	= array();
	$index	= 0;
	usort($all_items, 'date_cmp');
	foreach($all_items as $item) {
		//Only display the feeds from the chosen times
		if ( ($showtime>-1) && (time() - $item['date_timestamp'] > $showtime) ) {
			break;
		}
		if(isset($item['content']) && !empty($item['content'])) {
			$out[$index]['summary']	= $item['content'];
		}
		elseif(isset($item['summary']) && !empty($item['summary'])) {
			$out[$index]['summary']	= $item['summary'];
		}
		elseif(isset($item['description']) && !empty($item['description'])) {
			$out[$index]['summary']	= $item['description'];
		}
		else {
			$out[$index]['summary']	= _r('No summary specified');
		}
		$out[$index]['date']		= date('l d F, Y', $item['date_timestamp'] );
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
	call_hooked('return_output', $out);
	return lilina_parse_html($out);
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
	$html = file_get_contents($location);
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
		$final_links[$n] = $final_link;
	}
	//now figure out which one points to the RSS file
	for($n=0; $n<$link_count; $n++){
		if(strtolower($final_links[$n]['rel']) == 'alternate'){
			if(strtolower($final_links[$n]['type']) == 'application/rss+xml'){
				$href = $final_links[$n]['href'];
			}
			if(!$href and strtolower($final_links[$n]['type']) == 'text/xml'){
				//kludge to make the first version of this still work
				$href = $final_links[$n]['href'];
			}
			if($href){
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
		return $full_url;
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
	global $settings, $end_errors;
	$items		= array();
	$channels	= '';
	$index		= 0;
	$feeds	= $input['feeds'];
	foreach($feeds as $feed) {
		$rss	= fetch_rss( $feed['feed'] );
		if (!$rss){
			$end_errors	.= '<br />Could not fetch feed: ' . $feed['feed'] . '<br /> Magpie returned: ' . magpie_error();
			continue;
		}
		//Get the icon to display
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
	return array($channels, $items);
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
	global $settings;
	$config = HTMLPurifier_Config::createDefault();
	$config->set('Core', 'Encoding', $settings['encoding']); //replace with your encoding
	$config->set('Core', 'XHTML', true); //replace with false if HTML 4.01
	$config->set('HTML', 'Doctype', 'XHTML 1.0 Transitional');
	$config->set('Cache', 'SerializerPath', $settings['cachedir']);
	$purifier = new HTMLPurifier($config);
	if(is_array($val_array)) {
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
	return $purified_array;
}
?>