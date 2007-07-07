<?php
// $Id$
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
define('LILINA_PATH', dirname(__FILE__));
define('LILINA_INCPATH', LILINA_PATH . '/inc');
$settings	= 0;
$out		= '';
//Current Version
require_once(LILINA_INCPATH . '/core/version.php');

//Timer doesn't need settings so we don't have to wait for them
require_once(LILINA_INCPATH . '/core/misc-functions.php');
$timer_start = lilina_timer_start();

//Make sure we are actually installed...
require_once(LILINA_INCPATH . '/core/install-functions.php');
if(!lilina_check_installed()) {
	echo 'Lilina doesn\'t appear to be installed. Try <a href="install.php">installing it</a>';
	die();
}

//Plugins and misc stuff
require_once(LILINA_INCPATH . '/core/plugin-functions.php');

//We need this even for cached pages
require_once(LILINA_INCPATH . '/core/conf.php');

//Custom error handler
//require_once(LILINA_INCPATH . '/core/errors.php');

//Caching to reduce loading times
require_once(LILINA_INCPATH . '/core/cache.php');

//Localisation
require_once(LILINA_INCPATH . '/core/l10n.php');

// Do not update cache unless called with parameter force_update=1
if (isset($_GET['force_update']) && $_GET['force_update'] == 1) {
	define('MAGPIE_CACHE_AGE', 1);
}
else {
	lilina_cache_check();
}
//Require our standard stuff
require_once(LILINA_INCPATH . '/core/lib.php');

//Stuff for parsing Magpie output, etc
require_once(LILINA_INCPATH . '/core/feed-functions.php');

//Stuff for parsing Magpie output, etc
require_once(LILINA_INCPATH . '/core/file-functions.php');

$showtime = ( isset($_REQUEST['hours']) ? $_REQUEST['hours']*3600 : 3600*$settings['interface']['times'][0] ) ;

/*$data = lilina_load_feeds($settings['files']['feeds']);

// load times*/
$time_table	= lilina_load_times();
/*//CAUTION: Returns array
$list = lilina_make_items($data);
$out = lilina_make_output($list[1]);/*/
lilina_save_times($time_table);
lilina_cache_start();
/*
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/1">
<title><?php echo ($showtime=='-3600'? 'All Items | ' : 'Latest ' . $showtime/3600 . ' hours | '); echo $settings['sitename'];?></title>
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
	
</div><div style="text-align: right; padding-top: 0em; padding-right: 2em;" id="times">
<p style="font-size: 0.8em;">Show posts from the last:</p>
		<ul>
		<?php
		for($q=0;$q<count($settings['interface']['times']);$q++){
			$current_time = $settings['interface']['times'][$q];
			if($q == count($settings['interface']['times'])) {
				echo '<li class="last">';
			}
			else {
				echo '<li>';
			}
			if(is_int($current_time)){
				echo '<a href="index.php?hours='.$current_time.'"><span>'.$current_time.'h</span></a></li>';
			}
			else {
				switch($current_time) {
					case 'week':
						echo '<a href="index.php?hours=168"><span>week</span></a></li>';
					break;
					case 'all':
						echo '<a href="index.php?hours=-1"><span>all</span></a></li>';
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
	<?php if(isset($end_errors)) echo $end_errors; ?>
</div>
<div id="c1">&nbsp;powered by</div>
<div id="c2">&nbsp;lilina.</div>
<div id="footer">
  <p>Powered by <a href="http://lilina.cubegames.net/"><img src="i/logo.jpg" alt="lilina news aggregator" title="lilina news aggregator" /></a> v
	<?php echo $lilina['core-sys']['version']; ?><br />
	This page was last generated on
	<?php echo date('Y-m-d \a\t g:i a'); ?> and took
	<?php echo lilina_timer_end($timer_start); ?> seconds</p></div>
<img src="inc/templates/default/magpie.png" alt="Uses MagpieRSS" /><img src="inc/templates/default/oss.png" alt="Licensed under the GNU General Public License" /><img src="inc/templates/default/php.png" alt="Powered by PHP: Hypertext Processor" />
</body>
</html>
<?php
*/
require_once(LILINA_INCPATH . '/core/skin.php');
//require_once(LILINA_INCPATH . '/templates/' . $settings['template'] . '/index.php');
template_load();

lilina_cache_end();
?>