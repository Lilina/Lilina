<?php
/**
 * Default template for Lilina
 * @author Ryan McCue <cubegames@gmail.com>
 * @author Panayotis Vryonis <panayotis@vrypan.net>
 */
/**
*/
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/1">
<title><?php template_sitename();?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="<?php template_directory(); ?>/style.css" media="screen"/>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<script language="JavaScript" type="text/javascript" src="<?php template_siteurl(); ?>inc/js/jquery-1.2.1.pack.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php template_directory(); ?>/effects.js"></script>
<?php
template_header();
?>
</head>
<body class="river-page">
<div id="navigation">
  	<a href="<?php template_siteurl();?>">
	<img src="<?php template_directory(); ?>/logo-small.png" alt="<?php template_sitename();?>" title="<?php template_sitename();?>" />
	</a>
	<?php 
	if(template_synd_links())
		echo ' | ';
	?>
	<a id="expandall" href="javascript:void(0);"><img src="<?php template_directory(); ?>/arrow_out.png" alt="Show All Items" /> Expand</a> |
	<a id="collapseall" href="javascript:void(0);"><img src="<?php template_directory(); ?>/arrow_in.png" alt="Hide All Items" /> Collapse</a>
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

<div id="main">
<?php
$notfirst = false;
// We call it with false as a parameter to avoid incrementing the item number
if(has_items(false)) {
	while(has_items()): the_item();
	if(!date_equals()) {
		if($notfirst) {
			//Close both feed and date
			echo '		</div>';
			echo '	</div>', "\n";
		}
		else {
			$notfirst = true;
		}
?>
	<h1 title="Click to expand/collapse date">News stories from <?php the_date('format=l d F, Y'); ?></h1>
	<div id="date<?php the_date('format=dmY'); ?>">
		<div class="feed feed-<?php the_feed_id(); ?>">
<?php
	}
	elseif(!feed_equals()) {
		global $item_number;
		if($item_number != 0) {
			echo '		</div>';
		}
		echo '		<div class="feed feed-', get_the_feed_id(), '">';
	}
?>
			<div class="item c2" id="IITEM-<?php the_id(); ?>">
				<img src="<?php the_feed_favicon(); ?>" alt="Favicon for <?php the_feed_name();?>" title="Favicon for <?php the_feed_name();?>" style="width:16px; height:16px;" />
				<span class="time"><?php the_date('format=H:i'); ?></span>
				<span class="title" id="TITLE<?php the_id(); ?>" title="Click to expand/collapse item"><?php the_title(); ?></span>
				<span class="source"><a href="<?php the_link(); ?>">&#187; Post from <?php the_feed_name();?> <img src="<?php echo template_file_load('application_double.png'); ?>" alt="Visit off-site link" /></a></span>
				<div class="excerpt" id="ICONT<?php the_id(); ?>">
					<?php the_content(); ?>
					<?php if( has_enclosure() ){
						echo '<hr />', the_enclosure();
					} ?>
				</div>
				<?php do_action('river_entry'); ?>
			</div><?php

	endwhile;
}
elseif(!has_feeds()) {
?>
	<div style="border:1px solid #e7dc2b;background: #fff888;margin:15px;padding:10px;">You haven't added any feeds yet. Add them from <a href="admin.php">your admin panel</a></div>
<?php
}
else {
?>
	<div style="border:1px solid #e7dc2b;background: #fff888;margin:15px;padding:10px;">No items available from in the last <?php echo get_offset(true); ?> hour(s). Try <a href="index.php?hours=-1" id="viewallitems">viewing all items</a></div>
	<div style="border:1px solid #e7dc2b;background: #fff888;margin:15px;padding:10px;display:none;">Now loading all available items - If they don't load within 20 seconds, click <a href="index.php?hours=-1">here</a><br /><img src="<?php template_directory(); ?>/loading.gif" alt="Loading..." /></div>
<?php
}
?>
	</div>
</div>
</div>

<?php
if(has_feeds()) {
	?>
<div id="sources">
	<strong>Sources:</strong>
	<ul><?php list_feeds('format=<li><a href="%1$s">%3$s</a> [<a href="%4$s">' . _r('Feed') . "</a>]</li>\n"); ?>
	</ul>
</div>
<?php
}
else {
	//Already handled above; if there are no feeds, then there should be no items...
}
	?>
<div id="footer">
<p><?php echo get_option('sitename'); ?> is proudly powered by <a href="http://getlilina.org/">Lilina News Aggregator</a></p>
<!-- <?php global $timer_start; echo lilina_timer_end($timer_start); ?> -->
<?php template_footer(); ?>
</div>
</body>
</html>