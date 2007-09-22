<?php
/******************************************
		Lilina: Simple PHP Aggregator
File:		index.skin.php
Purpose:	Default Template
Notes:		
Style:		**EACH TAB IS 4 SPACES**
Licensed under the GNU General Public License
See LICENSE.txt to view the license
******************************************/
header('Content-Type: text/html; charset=utf-8');
global $settings, $showtime; //Just in case ;)
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/1">
<title><?php template_sitename();?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
template_synd_header();
?>
<link rel="stylesheet" type="text/css" href="<?php stylesheet_load('style.css'); ?>" media="screen"/>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<script language="JavaScript" type="text/javascript"><!--
	var showDetails = <?php echo (isset($_COOKIE['showDetails']) && ($_COOKIE['showDetails'] == 'true')) ? 'true' : 'false'; ?>;
	//var markID = '<?php //echo $_COOKIE['mark']; ?>' ;
//-->
</script>
<script language="JavaScript" type="text/javascript" src="js/engine.js"></script>
<?php
//Just extra stuff that a plugin may have added
call_hooked('template_header');
?>
</head>
<body onload="visible_mode(showDetails)">
<?php
call_hooked('body_top');
?>
<div id="navigation">
  	<a href="<?php template_siteurl();?>">
	<img src="i/logo.jpg" alt="<?php template_sitename();?>" title="<?php template_sitename();?>" />
	</a>
	<?php if(template_synd_links()) { echo '&nbsp;&nbsp; |'; } ?>
	<a href="javascript:visible_mode(true);">
	<img src="<?php template_path(); ?>/arrow_out.png" alt="<?php _e('Show All Items'); ?>" /> <?php _e('Expand'); ?></a>
	<a href="javascript:visible_mode(false);">
	<img src="<?php template_path(); ?>/arrow_in.png" alt="<?php _e('Hide All Items'); ?>" /> <?php _e('Collapse'); ?></a>
	|
	<a href="opml.php">OPML</a>
	|
	<a href="#sources">List of sources</a>
</div>

<div style="text-align: right; padding-top: 0em; padding-right: 2em;" id="times">
	<p style="font-size: 0.8em;">Show posts from the last:</p>
	<ul>
	<?php
	template_times();
	?>
	</ul>
</div>

<div id="main"><?php
$notfirst = false;
if(has_items()) {
	foreach(get_items() as $item) {
		if($item['date'] != $item['old_date']) {
			if($notfirst) {
				//Close both feed and date
				echo '		</div>';
				echo '	</div>', "\n";
			}
			$current_date = date('dmY', $item['timestamp'] );
	?>
	<h1><?php echo $item['date'];
	?><span style="float: right; margin-top: -1.3em;"><a href="javascript:void(0);" title="<?php _e('Click to expand/collapse date');
	?>" onclick="toggle_visible('date<?php echo $current_date;
	?>');toggle_hide_show('arrow<?php echo $current_date;
	?>'); return false;"><img src="i/arrow_in.png" alt="<?php _e('Hide Items from this date');
	?>" id="arrow<?php echo $current_date;
	?>" /></a></span></h1>
	<div id="date<?php echo $current_date;?>">
		<div class="feed" id="feed<?php echo md5($item['channel_link']), $current_date;?>">
		<?php
		}
		elseif(!isset($item['old_channel']) || $item['old_channel'] != $item['channel_link']) {
			if(isset($item['old_channel'])) {
				echo '		</div>';
			}
			echo '		<div class="feed" id="feed', md5($item['channel_link']), $current_date, '">';
		}
		?>
			<div class="item" id="IITEM-<?php echo $item['id'];?>"><img src="<?php echo $item['icon'];?>" alt="<?php _e('Favicon');?>" title="<?php _e('Favicon');?>" style="width:16px; height:16px;" />
				<span class="time"><?php echo $item['time'];?></span>
				<span class="title" id="TITLE<?php echo $item['id'];?>" title="<?php _e('Click to expand/collapse item');?>"><?php echo $item['title'];?></span>
				<span class="source"><a href="<?php echo $item['link'];?>">&#187; <?php _e('Post from'); ?> <?php echo $item['channel_title'];?> <img src="<?php template_path(); ?>/application_double.png" alt="<?php _e('Visit off-site link'); ?>" /></a></span>
				<?php
				if(!empty($item['enclosures'])){
					_e('Podcast or Videocast Available');
				}
				?>
				<div class="excerpt" id="ICONT<?php echo $item['id'];?>">
					<?php echo $item['summary'];?>
				</div>
			</div><?php
		//Feed closed above
	//Date closed above
	$notfirst = true;
	}
}
elseif(!has_feeds()) {
?>
	<div style="border:1px solid #e7dc2b;background: #fff888;margin:15px;padding:10px;">You haven't added any feeds yet. Add them from <a href="admin.php">your admin panel</a></div>
<?php
}
else {
?>
	<div style="border:1px solid #e7dc2b;background: #fff888;margin:15px;padding:10px;">No items available from in the last <?php echo ($showtime/3600); ?> hour(s). Try <a href="index.php?hours=-1">viewing all items</a></div>
<?php
}
?>
	</div>
</div>

<div id="sources">
	<strong>Sources:</strong>
	<ul><?php
		if(has_feeds()) {
			foreach(get_feeds() as $feed) { ?>
		<li><a href="<?php echo $feed['link']; ?>"><img src="<?php echo $feed['icon']; ?>" style="height:16px" alt="icon" />&nbsp; <?php echo $feed['name']; ?></a> <a href="<?php echo $feed['feed']; ?>">[Feed]</a></li><?php
			}
		}
		else {
			//Already handled above; if there are no feeds, then there should be no items...
		}
		?>
	</ul><?php
	$the_errors	= template_end_errors('var');
	if(!empty($the_errors)) { ?>
	<div id="end_errors">
	Errors occured while getting feeds:<?php echo $the_errors; ?>
	</div>
	<?php
	}
	?></div>
<div id="c1">&nbsp;powered by</div>
<div id="c2">&nbsp;lilina.</div>
<div id="footer">
<?php template_footer(); ?><br />
<img src="<?php template_path(); ?>/magpie.png" alt="Uses MagpieRSS" /><img src="<?php template_path(); ?>/oss.png" alt="Licensed under the GNU General Public License" /><img src="<?php template_path(); ?>/php.png" alt="Powered by PHP: Hypertext Processor" />
</div>
</body>
</html>