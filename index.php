<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		index.php
Purpose:	Main page
Notes:		Need to move all crud to plugins
			Maybe have only includes ALA WP.
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
//Stop hacking attempts
define('LILINA',1) ;
$data		= 'blank string';
$settings	= 0;
$out		= '';
//Timer doesn't need settings so we don't have to wait for them
require_once('./inc/core/misc-functions.php');
$timer_start = lilina_timer_start();
//Require our settings, must be second required file
//We need this even for cached pages
require_once('./inc/core/conf.php');
//Custom error handler
require_once('./inc/core/errors.php');
//Caching to reduce loading times
require_once('./inc/core/cache.php');
lilina_cache_start();
// Do not update cache unless called with parameter force_update=1
if (isset($_GET['force_update']) && $_GET['force_update']==1) {
	define('MAGPIE_CACHE_AGE', 1) ;
}
//Require our standard stuff
require_once('./inc/core/lib.php');
//Stuff for parsing Magpie output, etc
require_once('./inc/core/feed-functions.php');

$showtime = ( isset($_REQUEST['hours']) ? $_REQUEST['hours']*3600 : 3600*$settings['interface']['times'][0] ) ;

$data = lilina_load_feeds($settings['files']['feeds']) ;

// load times

if (file_exists($settings['files']['times'])) {
	$time_table = file_get_contents($settings['files']['times']) ;
	$time_table = unserialize($time_table) ;
} else {
	$time_table = array();
}
/*
$channel_list	= '<strong>'. $i18n['sources'] . '</strong>';
$channel_list	.= '<ul>';

for($i = 0; $i < count($data['feeds']); $i++) {
	$rss	= fetch_rss( $data['feeds'][$i]['feed'] );
	if (!$rss) continue;
	$ico	= channelFavicon( $rss->channel['link'] );
	$channel_list .= '<li><a href="' . $rss->channel['link'] . '">';
	$channel_list .= '<img src="'.$ico.'" style="height:16px" alt="icon" />&nbsp;';
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
}*/
//CAUTION: Returns array
$list = lilina_make_items($data);
usort($items, 'date_cmp');
/*
for($i=0;$i<count($items);$i++) {
	$item = $items[$i] ;
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
	// hook_before_sanitize();
	//Parse all variables so far
	parseHtml($title);
	parseHtml($channel_title);
	parseHtml($channel_url);
	parseHtml($ico);
	parseHtml($href);
	parseHtml($summary);
	// hook_after_sanitize();
	$this_date = date('D d F, Y', $item['date_timestamp'] ) ;
	$time = date('H:i', $item['date_timestamp'] ) ;
	if ($this_date!=$date) {
		if ($date) {
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
	$out	.= '<span class="title" id="TITLE'.$i.'">'.$title.'</span>' ;
	$out	.= '<span class="source"><a href="'.$href.'">&#187; Post from '.$channel_title.' <img src="i/application_double.png" alt="Visit off-site link" /></a></span>' ;
	if($enclosure){
		$out	.= 'Podcast or Videocast Available';
	}
	$out	.= '<div class="excerpt" id="ICONT'.$i.'">' ; 
	$out	.= $summary;
	if($SHOW_SOCIAL==true) {
	   $out .= delicious_tags($href) ;
	   $out .= "<br/><img src=\"i/delicious.gif\" alt=\"".$i18n['add_delicious']."\"/> <a href=\"javascript:deliciousPost('" . addslashes($href) ."','" . addslashes($title) . "');\">add to del.icio.us.</a>" ;
	   $out .= '&nbsp;<a href="http://del.icio.us/url/' . md5($href) .'">'.$i18n['look_delicious'].'</a>' . delicious_tags($href) ;
	}

	//$out .= google_get_res($title,0) ;

	$channel_url_old=$channel_url; 
  
	if($SHOW_SOCIAL==true) {
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
	}
	$out .= "</div>\n" ;
	$out .= "</div>\n" ;

	//Only display the feeds from the chosen times
	if ( ($showtime > -1) && (time() - $item['date_timestamp'] > $showtime) ) break ;
}
  if(count($items)!=0) $out .= '</div>' ;//Close the last "feed" div.*/
$out = lilina_make_output($list[1]);

lilina_save_times($time_table);

$itemCount = $i+1 ;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/1">
<title><?php echo $settings['sitename'];?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
if($settings['output']['rss']){
?>
<link rel="alternate" type="application/rss+xml" title="RSS Feed" href="rss.php" />
<?php
}
if($settings['output']['opml']){
?>
<link rel="alternate" type="application/rss+xml" title="OPML Feed" href="rss.php?output=opml" />
<?php
}
if($settings['output']['atom']){
?>
<link rel="alternate" type="application/rss+xml" title="Atom Feed" href="rss.php?output=atom" />
<?php
}
?>
<?php
//Add templates code here

?>
<link rel="stylesheet" type="text/css" href="<?php echo $settings['template_path']; ?>/style.css" media="screen"/>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<script language="JavaScript" type="text/javascript"><!--
	var showDetails = <?php echo ( ($_COOKIE['showDetails']=="true") ? "true" : "false"); ?>;
	var markID = '<?php echo $_COOKIE['mark']; ?>' ;
//-->
</script>
<script language="JavaScript" type="text/javascript" src="js/engine.js"></script>
</head>
<body onload="visible_mode(showDetails)">
<div id="navigation">
  	<a href="<?php echo $settings['baseurl']; ?>">
	<img src="i/logo.jpg" alt="<?php echo $settings['sitename'];?>" title="<?php echo $settings['sitename'];?>" />
	</a>
  	<?php if($settings['output']['rss']){?>RSS: <a href="rss.php"><img src="i/feed.png" alt="RSS feed" title="RSS feed" /></a><?php } ?>
  	<?php if($settings['output']['atom']){?>Atom: <a href="rss.php?output=atom"><img src="i/feed.png" alt="Atom feed" title="Atom feed" /></a><?php } ?>
	&nbsp;&nbsp;
	|
    <a href="javascript:visible_mode(true);">
	<img src="i/arrow_out.png" alt="Show All Items" /> Expand</a>
    <a href="javascript:visible_mode(false);">
	<img src="i/arrow_in.png" alt="Hide All Items" /> Collapse</a>
	|
    <a href="feeds/opml.xml">OPML</a>
	|
    <a href="#sources">SOURCES</a>
	
</div><div style="text-align: right; padding-top: 2em; padding-right: 2em;" id="times">
		<ul>
		<?php
		for($q=0;$q<count($settings['interface']['times']);$q++){
			$current_time = $settings['interface']['times'][$q];
			if(is_int($current_time)){
				echo '<li><a href="index.php?hours='.$current_time.'"><span>'.$current_time.'h</span></a></li>';
			}
			else {
				switch($current_time) {
					case 'week':
						echo '<li><a href="index.php?hours=168"><span>week</span></a></li>';
					break;
					case 'all':
						echo '<li><a href="index.php?hours=-1"><span>all</span></a></li>';
					break;
				}
			}
		}
		?>
		</ul>
    </div>
<div id="main">
<?php echo $out; ?>
</div>

<div id="sources">
	<strong>Sources:</strong>
	<ul>
    <?php echo $list[0]; ?>
	</ul>
	<?php echo $end_errors; ?>
</div>
<div id="c1">&nbsp;powered by</div>
<div id="c2">&nbsp;lilina.</div>
<div id="footer">
  <p>Powered by <a href="http://lilina.cubegames.net/"><img src="i/logo.jpg" alt="lilina news aggregator" title="lilina news aggregator" /></a> v
	<?php echo $lilina['core-sys']['version']; ?><br />
	This page was last generated on
	<?php echo date('Y-m-d \a\t g:i a'); ?> and took
	<?php echo lilina_timer_end($timer_start); ?> seconds</p></div>
</body>
</html>
<?php
/*
require_once('./inc/core/skin.php');
require_once('./templates/default/index.skin.php');*/
lilina_cache_end();
?>