<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		rss.php
Purpose:	RSS output
Notes:		Need to move all crud to plugins
			Thanks to Jean-Marc Liotier for
			this code
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
//Stop hacking attempts
define('LILINA',1) ;
//Require our settings, must be first required file
require_once('./inc/core/conf.php');

//Require our standard stuff
require_once('./inc/core/lib.php');






$TIMERANGE = ( $_REQUEST['hours'] ? $_REQUEST['hours']*3600 : 3600*24 ) ;

$data = file_get_contents($settings['files']['feeds']) ;
$data = unserialize( base64_decode($data) ) ;

$items = array();

// load times

if (file_exists($settings['files']['times'])) {
	$time_table = file_get_contents($settings['files']['times']) ;
	$time_table = unserialize($time_table) ;
} else {
	$time_table = array();
}


for($i = 0; $i < count($data['feeds']); $i++) {
	$rss	= fetch_rss( $data['feeds'][$i]['feed'] );
	if (!$rss) continue;
	$ico	= channelFavicon( $rss->channel['link'] );
	$channel_list .= '<li><a href="' . $rss->channel['link'] . '">';
	$channel_list .= '<img src="'.$ico.'" style="height:16px" alt="icon"/>';
	if(!$data['feeds'][$i]['name']){
		$channel_list .= $rss->channel['title'] . '</a></li>';
	}
	else {
		$channel_list .= $data['feeds'][$i]['name'] . '</a></li>';
	}
	for ( $j=0; $j < count($rss->items); $j++) {
		$x = $rss->items[$j] ;
		if(!$data['feeds'][$i]['name']){
			$x['channel_title'] .= $rss->channel['title'];
		}
		else {
			$x['channel_title'] .= $data['feeds'][$i]['name'];
		}
		$x['channel_url'] = $rss->channel['link'] ;
		$x['favicon'] = $ico ;
		if ($x['date_timestamp'] == '') {
			$x['date_timestamp'] = create_time($x['title'] . $x['link']);
		}
		else {
			$x['date_timestamp'] .= $settings['offset'] * 60 * 60;
		}
		$items[] = $x ;
	}
}

include("./inc/contrib/feedcreator.class.php");

$rss_out = new UniversalFeedCreator();
$rss_out->useCached(); // use cached version if age<1 hour
$rss_out->title = $settings['sitename'];
$rss_out->description = $settings['baseurl'];

//optional
$rss_out->descriptionTruncSize = 500;
$rss_out->descriptionHtmlSyndicated = true;

$rss_out->link = $settings['baseurl'];
$rss_out->syndicationURL = $settings['baseurl'].$_SERVER["PHP_SELF"];

//$image = new FeedImage();
//$image->title = $settings['sitename'];
//$image->url = $settings['baseurl'].'/i/logo.jpg";
//$image->link = "$BASEURL";
//$image->description = "$SITETITLE";

//optional
//$image->descriptionTruncSize = 500;
//$image->descriptionHtmlSyndicated = true;

//$rss_out->image = $image;

usort($items, 'date_cmp');
for($i=0;$i<count($items);$i++) {

   $item = $items[$i] ;

   $item_out = new FeedItem();
   
   $item_out->title = $item['title'];
   $item_out->link = $item['link'];
   $item_out->source = $items->channel_title;
   $item_out->description = $item['content'];
   if(!$item_out->description) $item_out->description = $item['summary'];
   if(!$item_out->description) $item_out->description = $item['description'];
	$item_out->date = date('D d F, Y', $item['date_timestamp'] ) ;

   //item->descriptionTruncSize = 500;
   $item_out->descriptionHtmlSyndicated = true;

   $rss_out->addItem($item_out);
}

// valid format strings are: RSS0.91, RSS1.0, RSS2.0, PIE0.1 (deprecated),
// MBOX, OPML, ATOM, ATOM0.3, HTML, JS
if($settings['output']['atom'] == true) {
	echo $rss_out->saveFeed("ATOM", "feeds/atom.xml");
}
if($settings['output']['opml'] == true) {
	echo $rss_out->saveFeed("OPML", "feeds/opml.xml");
}
if($settings['output']['rss'] == true) {
	echo $rss_out->saveFeed("RSS2.0", "feeds/feed.xml");
}

// save times
$ttime = serialize($time_table);
$fp = fopen($settings['files']['times'],'w') ;
fputs($fp, $ttime) ;
fclose($fp) ;
?>
