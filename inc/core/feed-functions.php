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
function lilina_make_item($item, $date) {
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

	$channel_url_old=$channel_url; 
  
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
	}*/
	$out .= "</div>\n" ;
	$out .= "</div>\n" ;
	return array($out, $date);
}

function lilina_make_output($all_items) {
	usort($all_items, 'date_cmp');
	foreach($all_items as $item) {
	//for($i=0;$i<count($items);$i++) {
		$next		= array();
		////Note: returns array
		//$next		= lilina_make_item($items[$i], $the_date);
		//First enclosure listed is the one displayed
		$enclosure		= $item['enclosures'][0]['url'];
		$enclosuretype	= $item['enclosures'][0]['type'];
		$summary		= '' ;
		echo '<pre>';
		print_r($item);
		echo '</pre>';
		$channel_title	= $item['channel_title'];
		$channel_url	= $item['channel_link'];  
		$ico			= $item['favicon'] ;
		$href			= (empty($item['link'])) ? $href = $item['guid'] : $item['link'];
		$item_id		= md5($href . $channel_url) ;
		$title			= $item['title'];
		$summary		= (empty($item['content'];
		if(!$summary){
			$summary	= $item['summary'];
		}
		// before_sanitize();
		//Parse all variables so far
		/*lilina_parse_html(
							array(
									$title,
									$channel_title,
									$channel_url,
									$ico,
									$href,
									$summary
								)
						);*/
		// after_sanitize();
		$this_date		= date('D d F, Y', $item['date_timestamp'] ) ;
		echo 'This_date: ' . $this_date . ' End This_date;';
		$time			= date('H:i', $item['date_timestamp'] ) ;
		if ($this_date != $date) {
			//If this isn't the first date...
			if ($date) {
				//End the last date's div
				$out	.= '</div>' ;
				$channel_url_old	= '' ;
			}
			$date 	= $this_date ;
			// call_hooked('date');
			$out		.= '<h1>'.$date;
			$out		.= '<span style="float: right; margin-top: -1.3em;">';
			$out		.= '<a href="javascript:void(0);" onclick="toggle_visible(\'date' . date('dmY', $item['date_timestamp'] );
			$out		.= '\');toggle_hide_show(\'arrow';
			$out		.= date('dmY', $item['date_timestamp'] );
			$out		.= '\'); return false;"><img src="i/arrow_in.png" alt="Hide Items from this date" id="arrow';
			$out		.= date('dmY', $item['date_timestamp'] );
			$out		.= '" /></a></span>';
			$out		.= '</h1><div id="date';
			$out		.= date('dmY', $item['date_timestamp'] );
			$out		.= '">';
			$out		.= "\n" ;
		}
		//global $date;
		if ($channel_url_old != $channel_url) {
			if ($channel_url_old){
				$out	.= '</div>' ;
			}
			$out		.= '<div class="feed">' ;
		}
		$out			.= '<div class="item" id="IITEM-'.$item_id.'">' ;

		if ($ico){
			$out		.= '<img src="'.$ico.'" alt="Favicon" title="'.$i18n['favicon'].'" style="width:16px; height:16px;" />' ;
		}
		$out			.= '<span class="time">'.$time.'</span>' ;
		$out			.= '<span class="title" id="TITLE'.$item_id.'">'.$title.'</span>' ;
		$out			.= '<span class="source"><a href="'.$href.'">&#187; Post from '.$channel_title.' <img src="i/application_double.png" alt="Visit off-site link" /></a></span>' ;
		if($enclosure){
			$out		.= 'Podcast or Videocast Available';
		}
		$out			.= '<div class="excerpt" id="ICONT'.$item_id.'">' ; 
		$out			.= $summary;
		$channel_url_old	= $channel_url;
		$out			.= "</div>\n" ;
		$out			.= "</div>\n" ;
		$out			.= $next[0];
		$the_date		= $next[1];
		//Only display the feeds from the chosen times
		if ( ($showtime>-1) && (time() - $item['date_timestamp'] > $showtime) ) {
			break;
		}
	}
	if(count($all_items)!=0) {
		$out		.= '</div>' ;//Close the last "feed" div.
	}
	lilina_parse_html($out);
	return $out;
}

function lilina_get_rss($location) {
/*
	lilina_get_rss() was found at 
	http://keithdevens.com/weblog/archive/2002/Jun/03/RSSAuto-DiscoveryPHP
*/
    if(!$html or !$location){
        return false;
    }else{
        //search through the HTML, save all <link> tags
        //and store each link's attributes in an associative array
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
                if(!$href and strtolower($final_links[$n]['type']) == 'application/atom+xml'){
                    //kludge to make the first version of this still work
                    $href = $final_links[$n]['href'];
                }
                if($href){
                    if(strstr($href, "http://") !== false){ //if it's absolute
                        $full_url = $href;
                    }else{ #otherwise, 'absolutize' it
                        $url_parts = parse_url($location);
						// echo "<pre>" ; print_r($url_parts) ; echo "</pre>" ;
                        //only made it work for http:// links. Any problem with this?
                        $full_url = "http://$url_parts[host]";
                        if(isset($url_parts['port'])){
                            $full_url .= ":$url_parts[port]";
                        }
                        if($href{0} != '/'){ //it's a relative link on the domain
                               if (substr($url_parts['path'],-1)!='/') {
                                        $full_url .= dirname($url_parts['path']);
                                } else {
                                        $full_url .= $url_parts['path'] ;
                                }
                        }
                        $full_url .= $href;
                    }
                    return $full_url;
                }
            }
        }
        return false;
    }
}

function lilina_make_items($input) {
	global $settings;
	$items	= array();
	$feeds	= $input['feeds'];
	foreach($feeds as $feed) {
		$rss	= fetch_rss( $feed['feed'] );
		if (!$rss){
			continue;
		}
		$ico	= channelFavicon( $rss->channel['link'] );
		$channel_list .= '<li><a href="' . $rss->channel['link'] . '">';
		$channel_list .= '<img src="'.$ico.'" style="height:16px" alt="icon" />&nbsp;';
		if(!$feed['name']){
			$channel_list .= $rss->channel['title'] . '</a></li>';
		}
		else {
			$channel_list .= $feed['name'] . '</a></li>';
		}
		foreach($rss->items as $item){
			if(!$feed['name']){
				$item['channel_title']	.= $rss->channel['title'];
			}
			else {
				$item['channel_title']	.= $input['feeds'][$i]['name'];
			}
			$item['channel_url']		= $rss->channel['link'] ;
			$item['favicon']			= $ico ;
			if ($x['date_timestamp'] == '') {
				$item['date_timestamp']	= create_time($item['title'] . $item['link']);
			}
			else {
				$item['date_timestamp']	.= $settings['offset'] * 60 * 60;
			}
			$items[] = $item ;
		}
	}
	return array($channel_list, $items);
}
// feed-functions.php, line 38-43
function lilina_parse_html($val_array){
	global $settings;
	if($settings['encoding']!='utf-8'){
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Core', 'Encoding', 'utf-8'); //replace with your encoding
		$config->set('Core', 'XHTML', true); //replace with false if HTML 4.01
		$purifier = new HTMLPurifier($config);
		}
    else {
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Core', 'Encoding', $settings['encoding']); //replace with your encoding
		$config->set('Core', 'XHTML', true); //replace with false if HTML 4.01
		$purifier = new HTMLPurifier();
		}
	if(is_array($val_array)) {
		$val_array = $purifier->purifyArray($val_array);
	}
	else {
		$val_array = $purifier->purify($val_array);
	}
	return $val_array;
}
?>