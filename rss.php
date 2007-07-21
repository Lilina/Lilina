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
define('LILINA_PATH', dirname(__FILE__));
define('LILINA_INCPATH', LILINA_PATH . '/inc');
//Require our settings, must be first required file
require_once('./inc/core/conf.php');

//Require our standard stuff
require_once('./inc/core/lib.php');

//Stuff for parsing Magpie output, etc
require_once('./inc/core/feed-functions.php');

//File input and output
require_once('./inc/core/file-functions.php');

//Get the feed creator loaded
require_once('./inc/contrib/feedcreator.class.php');

$display	= (isset($_GET['output'])) ? strtolower($_GET['output']) : 'rss';
//echo '<!--Display: ' . $display . ' - 1 -->';
$showtime	= (isset($_REQUEST['hours'])) ? $_REQUEST['hours'] * 3600 : 3600 * $settings['interface']['times'][0] ;

$data = lilina_load_feeds($settings['files']['feeds']) ;

$items = array();

// load times

if (file_exists($settings['files']['times'])) {
	$time_table = file_get_contents($settings['files']['times']) ;
	$time_table = unserialize($time_table) ;
} else {
	$time_table = array();
}



$items = lilina_return_items($data);
$items = $items[1];

$rss_out = new UniversalFeedCreator();
switch($display) {
	case 'opml':
		$rss_out->useCached('OPML', $settings['cachedir'] . 'opml.xml', $settings['cachetime']);
		break;
	case 'atom':
		$rss_out->useCached('ATOM', $settings['cachedir'] . 'atom.xml', $settings['cachetime']);
		break;
	case 'rss':
	default:
		$rss_out->useCached('RSS2.0', $settings['cachedir'] . 'feed.xml', $settings['cachetime']);
		break;
}
$rss_out->title = $settings['sitename'];
$rss_out->description = $settings['baseurl'];

//optional
$rss_out->descriptionTruncSize = 500;
$rss_out->descriptionHtmlSyndicated = true;

$rss_out->link = $settings['baseurl'];
$rss_out->syndicationURL = $settings['baseurl'] . $_SERVER['PHP_SELF'];

//$image = new FeedImage();
//$image->title = $settings['sitename'];
//$image->url = $settings['baseurl'].'/i/logo.jpg";
//$image->link = $settings['baseurl'];
//$image->description = $settings['sitename'];

//optional
//$image->descriptionTruncSize = 500;
//$image->descriptionHtmlSyndicated = true;

//$rss_out->image = $image;

$items = lilina_return_output($items);
usort($items, 'date_cmp');
foreach($items as $item) {
   $item_out = new FeedItem();
   
   $item_out->title = $item['title'];
   $item_out->link = $item['link'];
   $item_out->source = $item['channel_title'];
   $item_out->description = $item['summary'];
   if(!$item_out->description) $item_out->description = $item['summary'];
   if(!$item_out->description) $item_out->description = $item['description'];
	$item_out->date = date('D d F, Y', $item['timestamp'] ) ;

   //item->descriptionTruncSize = 500;
   $item_out->descriptionHtmlSyndicated = true;

   $rss_out->addItem($item_out);
}


// valid format strings are: RSS0.91, RSS1.0, RSS2.0, PIE0.1 (deprecated),
// MBOX, OPML, ATOM, ATOM0.3, HTML, JS
if($settings['output']['atom'] == true) {
	$rss_out->saveFeed('ATOM', $settings['cachedir'] . 'atom.xml', false);
}
if($settings['output']['rss'] == true) {
	$rss_out->saveFeed('RSS2.0', $settings['cachedir'] . 'feed.xml', false);
}

switch($display) {
	case 'atom':
		echo $rss_out->createFeed('ATOM');
		break;
	case 'rss':
	default:
		echo $rss_out->createFeed('RSS2.0');
		break;
}

lilina_save_times($time_table);
?>