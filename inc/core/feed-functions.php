<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		feed-functions.php
Purpose:	Functions that work with feeds
			and Magpie
Notes:		
Functions:	lilina_time_start();
			lilina_time_end( $timer_start_time );
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
defined('LILINA') or die('Restricted access');
/*function lilina_make_item($item, $date) {
	//First enclosure listed is the one displayed
	$enclosure = $item['enclosures'][0]['url'];
	$enclosuretype = $item['enclosures'][0]['type'];
	$summary = "" ;
	//echo '<pre>';
	//print_r($item);
	//echo '</pre>';
	$channel_title = $item['channel_title'];
	$channel_url = $item['channel_link'];  
	$ico = $item['favicon'] ;
	$href = $item['link'];
	if (!$href) {
		$href = $item['guid'];
	}
	$item_id = md5($href.$channel_url) ;
	$title = $item['title'];
	$summary = $item['content'];
	if(!$summary){
		$summary = $item['summary'];
	}
	// before_sanitize();
	//Parse all variables so far
	lilina_parse_html(
						array(
								$title,
								$channel_title,
								$channel_url,
								$ico,
								$href,
								$summary
							)
					);
	// after_sanitize();
	$this_date = date('D d F, Y', $item['date_timestamp'] ) ;
	echo 'This_date: ' . $this_date . ' End This_date;';
	$time = date('H:i', $item['date_timestamp'] ) ;
	if ($this_date!=$date) {
		//If this isn't the first date...
		if ($date) {
			//End the last date's div
			$out .= '</div>' ;
			$channel_url_old	= '' ;
		}

		$date 	= $this_date ;
		// hook_date();
		$out	.= '<h1>'.$date;
		$out	.= '<span style="float: right; margin-top: -1.3em;">';
		$out	.= '<a href="javascript:void(0);" onclick="toggle_visible(\'date';
		$out	.= date('dmY', $item['date_timestamp'] );
		$out	.= '\');toggle_hide_show(\'arrow';
		$out	.= date('dmY', $item['date_timestamp'] );
		$out	.= '\'); return false;"><img src="i/arrow_in.png" alt="Hide Items from this date" id="arrow';
		$out	.= date('dmY', $item['date_timestamp'] );
		$out	.= '" /></a></span>';
		$out	.= '</h1><div id="date';
		$out	.= date('dmY', $item['date_timestamp'] );
		$out	.= '">';
		$out	.= "\n" ;
	}
	global $date;
	if ($item_id==$_COOKIE['mark']) $markStatus	= 'on' ;
	else $markStatus	= 'off';


	if ($channel_url_old != $channel_url) {
		if ($channel_url_old){
			$out	.= '</div>' ;
		}
		$out	.= '<div class="feed">' ;
	}
	$out	.= '<div class="item" id="IITEM-'.$item_id.'">' ;
 
	if ($ico){
		$out	.= '<img src="'.$ico.'" alt="Favicon" title="'.$i18n['favicon'].'" style="width:16px; height:16px;" />' ;
	}
	$out	.= '<span class="time">'.$time.'</span>' ;
	$out	.= '<span class="title" id="TITLE'.$item_id.'">'.$title.'</span>' ;
	$out	.= '<span class="source"><a href="'.$href.'">&#187; Post from '.$channel_title.' <img src="i/application_double.png" alt="Visit off-site link" /></a></span>' ;
	if($enclosure){
		$out	.= 'Podcast or Videocast Available';
	}
	$out	.= '<div class="excerpt" id="ICONT'.$item_id.'">' ; 
	$out	.= $summary;
	/*if($SHOW_SOCIAL==true) {
	   $out .= delicious_tags($href) ;
	   $out .= "<br/><img src=\"i/delicious.gif\" alt=\"".$i18n['add_delicious']."\"/> <a href=\"javascript:deliciousPost('" . addslashes($href) ."','" . addslashes($title) . "');\">add to del.icio.us.</a>" ;
	   $out .= '&nbsp;<a href="http://del.icio.us/url/' . md5($href) .'">'.$i18n['look_delicious'].'</a>' . delicious_tags($href) ;
	}*/

	//$out .= google_get_res($title,0) ;

	// $channel_url_old=$channel_url; 
  
  /*if($SHOW_SOCIAL==true) {
	   $out .= ' &nbsp; <a href="javascript:furlPost(\''.$href.'\',\''.$title.'\');" title="'.$i18n['furl'].'">
     <img src="i/furl.gif" alt="'.$i18n['furl'].'"/></a>' ;
	   $out .= ' &nbsp; <a href="http://digg.com/submit?phase=2&amp;url='.$href.'&amp;title='.$title.'" target="_blank" title="digg this">
     <img src="i/digg.gif" alt="digg this"/></a>';
	   $out .= ' &nbsp; <a href="javascript:slashdotPost(\''.$href.'\',\''.$title.'\');" title="Submit to Slashdot">
     <img src="i/slashdot.gif" alt="Submit to Slashdot" /></a>';
	   $out .= ' &nbsp; <a href="http://www.blinklist.com/index.php?Action=Blink/addblink.php&amp;Quick=true&amp;Url='.$href.'&amp;Title='.$title.'" target="_blank" title="Add to Blinklist">
     <img src="i/blinklist.gif" alt="add to blinklist" /></a>';
     $out .= ' &nbsp; <a href="javascript:spurlPost(\''.$href.'\',\''.$title.'\');" title="Spurl this">
     <img src="i/spurl.gif" alt="Spurl this" /></a>';
     $out .= ' &nbsp; <a href="https://favorites.live.com/quickadd.aspx?marklet=1&amp;mkt=en-us&amp;url='
     . $href . '&amp;title=' . $title .'&amp;top=1" target="_blank" title="Add to Windows Live Bookmarks">
     <img src="i/winlive.gif" alt="Add to Windows Live Bookmarks" /></a>';
     $out .= ' &nbsp; <a href="http://technorati.com/cosmos/search.html?url=' .$href.'" target="_blank" title="Add to Technorati">
     <img src="i/technorati.gif" alt="Add to Technorati" /></a>';
     $out .= ' &nbsp; <a href="http://reddit.com/submit?url='.$href.'&amp;title='.$title.'" target="_blank" title="Add to reddit">
     <img src="i/reddit.gif" alt="Add to reddit" /></a>';
     $out .= ' &nbsp; <a href="http://www.newsvine.com/_tools/seed&amp;save?u='.$href.'&amp;h='.$title.'" target="_blank" title="Add to newsvine">
     <img src="i/newsvine.gif" alt="Add to newsvine" /></a>';
	}*-/
	$out .= "</div>\n" ;
	$out .= "</div>\n" ;
	return array($out, $date);
}*/

function lilina_make_output($all_items) {
	global $showtime, $settings;
	$out	= '';
	$date	= '';
	usort($all_items, 'date_cmp');
	foreach($all_items as $item) {
		//First enclosure listed is the one displayed
		if(isset($item['enclosures']) && is_array($item['enclosures'])){
			$enclosure		= $item['enclosures'][0]['url'];
			$enclosuretype	= $item['enclosures'][0]['type'];
		}
		$summary		= '' ;
		$channel_title	= $item['channel_title'];
		$channel_url	= $item['channel_url']; 
		$ico			= $item['favicon'] ;
		$href			= (empty($item['link'])) ? $item['guid'] : $item['link'];
		$item_id		= md5($href . $channel_url) ;
		$title			= $item['title'];
		if(isset($item['content']) && !empty($item['content'])) {
			$summary	= $item['content'];
		}
		elseif(isset($item['summary']) && !empty($item['summary'])) {
			$summary	= $item['summary'];
		}
		elseif(isset($item['description']) && !empty($item['description'])) {
			$summary	= $item['description'];
		}
		else {
			$summary	= _r('No summary specified');
		}
		$this_date		= date('D d F, Y', $item['date_timestamp'] ) ;
		$time			= date('H:i', $item['date_timestamp'] ) ;
		if ($this_date != $date) {
			//If this isn't the first date...
			if (empty($date)) {
				//End the last date's div
				$out	.= '</div>' ;
				$channel_url_old	= '' ;
			}
			$date 	= $this_date ;
			$out		.= '<h1>'.$date;
			$out		.= '<span style="float: right; margin-top: -1.3em;">';
			$out		.= '<a href="javascript:void(0);" title="';
			$out		.= _r('Click to expand/collapse date');
			$out		.= '" onclick="toggle_visible(\'date' . date('dmY', $item['date_timestamp'] );
			$out		.= '\');toggle_hide_show(\'arrow';
			$out		.= date('dmY', $item['date_timestamp'] );
			$out		.= '\'); return false;"><img src="i/arrow_in.png" alt="';
			$out		.= _r('Hide Items from this date') . '" id="arrow';
			$out		.= date('dmY', $item['date_timestamp'] );
			$out		.= '" /></a></span>';
			$out		.= '</h1><div id="date';
			$out		.= date('dmY', $item['date_timestamp'] );
			$out		.= '">';
			$out		.= "\n" ;
		}
		if (!isset($channel_url_old) || $channel_url_old != $channel_url) {
			if (isset($channel_url_old)) {
				$out	.= '</div>' ;
			}
			$out		.= '<div class="feed">' ;
		}
		$out			.= '<div class="item" id="IITEM-'.$item_id.'">' ;

		if ($ico){
			$out		.= '<img src="'.$ico.'" alt="'._r('Favicon').'" title="'._r('Favicon').'" style="width:16px; height:16px;" />' ;
		}
		$out			.= '
<span class="time">'.$time.'</span>
<span class="title" id="TITLE'.$item_id.'" title="'._r('Click to expand/collapse item').'">'.$title.'</span>
<span class="source"><a href="'.$href.'">&#187; '. _r('Post from') . ' ' . $channel_title.' <img src="i/application_double.png" alt="'. _r('Visit off-site link') .'" /></a></span>' ;
		if(isset($enclosure) && !empty($enclosure)){
			$out		.=  _r('Podcast or Videocast Available');
		}
		$out			.= '<div class="excerpt" id="ICONT'.$item_id.'">' ; 
		$out			.= $summary;
		$channel_url_old	= $channel_url;
		$out			.= "</div>\n" ;
		$out			.= "</div>\n" ;
		//Only display the feeds from the chosen times
		if ( ($showtime>-1) && (time() - $item['date_timestamp'] > $showtime) ) {
			break;
		}
	}
	if(count($all_items)!=0) {
		$out		.= '</div>' ;//Close the last "feed" div.
	}
	else {
		$out		.= '<div style="border:1px solid #e7dc2b;background: #fff888;">You haven\'t added any feeds yet. Add them from <a href="admin.php">your admin panel</a></div>';
	}
	lilina_parse_html($out);
	return $out;
}

function lilina_return_output($all_items) {
	global $showtime, $settings;
	$out	= array();
	$index	= 0;
	usort($all_items, 'date_cmp');
	foreach($all_items as $item) {
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
		$out[$index]['date']		= date('D d F, Y', $item['date_timestamp'] );
		$out[$index]['old_date']	= ($index != 0) ? $out[$index-1]['date'] : '';
		$out[$index]['time']		= date('H:i', $item['date_timestamp'] ) ;
		$out[$index]['timestamp']	= $item['date_timestamp'];
		$out[$index]['channel_link']= $item['channel_url']; 
		$out[$index]['link']		= (!isset($item['link']) || empty($item['link'])) ? $item['guid'] : $item['link'];
		$out[$index]['old_channel']	= (isset($channel_url_old)) ? $channel_url_old : '' ;
		$out[$index]['id']			= md5($out[$index]['link'] . $out[$index]['channel_link']);
		$out[$index]['icon']		= (isset($item['favicon'])) ? $item['favicon'] : '' ;
		$out[$index]['title']		= $item['title'];
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
		//Only display the feeds from the chosen times
		if ( ($showtime>-1) && (time() - $item['date_timestamp'] > $showtime) ) {
			break;
		}
		$index++;
	}
	return lilina_parse_html($out);
}

/**
* Retrieve available feeds for a given page
*
* Originally by Keith Devens; includes improvements by "Cristian"
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
* Takes an array of feeds and makes a HTML list of feeds and an array of all items
*
* Takes an input array and parses it using the Magpie library. Makes an HTML unordered list
* consisting of the feed's favicon, the name and the link. Takes the items returned by Magpie
* and adds the favicon, fixes the timestamp and adds the channel information. Deprecated in
* favour of lilina_return_items
* @deprecated
* @param array $input See lilina_return_items
* @return array See lilina_return_items
*/
function lilina_make_items($input) {
	global $settings, $end_errors;
	$items	= array();
	$channel_list	= '';
	$feeds	= $input['feeds'];
	foreach($feeds as $feed) {
		$rss	= fetch_rss( $feed['feed'] );
		if (!$rss){
			$end_errors	.= '<br />Could not fetch feed: ' . $feed['feed'] . '<br /> Magpie returned: ' . magpie_error();
			continue;
		}
		//Get the icon to display
		$ico	= channel_favicon( $rss->channel['link'] );
		//Add it to the list
		$channel_list .= '<li><a href="' . $rss->channel['link'] . '">';
		$channel_list .= '<img src="'.$ico.'" style="height:16px" alt="icon" />&nbsp;';
		if(!$feed['name']){
			//User hasn't specified name, get it ourselves
			$channel_list .= $rss->channel['title'] . '</a> <a href="' . $feed['feed'] . '">[Feed]</a></li>';
		}
		else {
			//Use supplied name
			$channel_list .= $feed['name'] . '</a> <a href="' . $feed['feed'] . '">[Feed]</a></li>';
		}
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
				$item['channel_title']	= $rss->channel['title'];
			}
			$item['channel_url']		= $rss->channel['link'] ;
			$item['favicon']			= $ico ;
			if (empty($item['date_timestamp']) || !isset($item['date_timestamp'])) {
				//No date set
				if($item['pubdate']) {
					//It's set in a different way by the feed, lets use it
					$item['date_timestamp'] = strtotime($item['pubdate']);
					if(!$item['date_timestamp']){
						//OK, we lied, that doesn't work either
						$item['date_timestamp']	= create_time($item['title'] . $item['link']);
					}
				}
				elseif($the_item['dc']['date']) {
					//Support for Dublin Core
					$the_item['date_timestamp']	= parse_w3cdtf($the_item['dc']['date']);
				}
				else {
					//This feed doesn't like us
					$item['date_timestamp']	= create_time($item['title'] . $item['link']);
				}
			}
			$item['date_timestamp']	+= $settings['offset'] * 60 * 60;
			$items[] = $item ;
		}
	}
	return array($channel_list, $items);
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
			if (empty($item['date_timestamp']) || !isset($item['date_timestamp'])) {
				//No date set
				if($item['pubdate']) {
					//It's set in a different way by the feed, lets use it
					$item['date_timestamp'] = strtotime($item['pubdate']);
					if(!$item['date_timestamp']){
						//OK, we lied, that doesn't work either
						$item['date_timestamp']	= create_time($item['title'] . $item['link']);
					}
				}
				elseif($the_item['dc']['date']) {
					//Support for Dublin Core
					$the_item['date_timestamp']	= parse_w3cdtf($the_item['dc']['date']);
				}
				else {
					//This feed doesn't like us
					$item['date_timestamp']	= create_time($item['title'] . $item['link']);
				}
			}
			$item['date_timestamp']	+= $settings['offset'] * 60 * 60;
			$items[] = $item ;
		}
		$index++;
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
	$config->set('Cache', 'SerializerPath', $settings['cachedir']);
	$purifier = new HTMLPurifier($config);
	if(is_array($val_array)) {
		$val_array = $purifier->purifyArray($val_array);
	}
	else {
		$val_array = $purifier->purify($val_array);
	}
	return $val_array;
}
?>