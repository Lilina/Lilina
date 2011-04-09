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
<!DOCTYPE html>
<html>
<head>
	<title><?php template_sitename();?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo Templates::get_template_dir_url(); ?>/style.css" media="screen"/>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<?php
	template_header();
	?>
</head>
<body class="river-page">
	<div id="navigation">
		<a href="<?php template_siteurl();?>">
		<img src="<?php template_siteurl() ?>admin/logo-small.png" alt="<?php template_sitename();?>" title="<?php template_sitename();?>" />
		</a>
		<a href="?method=feed&amp;type=rss2"><?php _e('RSS Feed') ?></a>
		<a href="?method=feed&amp;type=atom"><?php _e('Atom Feed') ?></a> |
		<a href="<?php echo get_option('baseurl') ?>?method=opml"><?php _e('OPML'); ?></a>
		|
		<a href="#sources"><?php _e('List of sources'); ?></a>
		|
		<a href="admin/"><?php _e('Admin'); ?></a>
	</div>

	<div id="times">
		<p><?php _e('Show posts from the last:'); ?></p>
		<ul>
			<li><a href="index.php?hours=24"><?php printf(_r('Past %d hours'), 24) ?></a></li>
			<li><a href="index.php?hours=48"><?php printf(_r('Past %d hours'), 48) ?></a></li>
			<li><a href="index.php?hours=168"><?php _e('Past week') ?></a></li>
			<li class="last"><a href="index.php?hours=-1"><span><?php _e('Show all') ?></span></a></li>
		</ul>
	</div>

	<div id="main">
	<?php
	$num = 0;
	if(has_items()) {
		while(has_items()): the_item();
	?>
		<?php the_date('before=<h1 title="' . _r('Click to expand/collapse date') . '" class="date">' . _r('News stories from') . ' &after=</h1>&format=l d F, Y') ?>
		<div class="item c2 feed-<?php the_feed_id(); ?>" id="item-<?php echo $num++ ?>">
			<img src="<?php the_feed_favicon(); ?>" alt="<?php printf(_r('Favicon for %s'), get_the_feed_name()) ?>" title="<?php printf(_r('Favicon for %s'), get_the_feed_name()) ?>" style="width:16px; height:16px;" />
			<span class="time"><?php the_time('format=H:i'); ?></span>
			<span class="title" title="<?php _e('Click to expand/collapse item') ?>"><?php the_title(); ?></span>
			<span class="source"><a href="<?php the_link(); ?>">&#187; <?php printf(_r('Post from %s'), get_the_feed_name()); ?> <img src="<?php echo template_file_load('application_double.png'); ?>" alt="<?php _e('Visit off-site link') ?>" /></a></span>
	<?php
			if( has_enclosure() ){
	?>
			<span class="enclosure"><?php the_enclosure(); ?></span>
	<?php
			}
	?>
			<div class="excerpt">
				<?php the_content(); ?>
			</div>
			<?php do_action('river_entry'); ?>
			<?php action_bar('before=&after= | '); ?>
		</div><?php
		endwhile;
	}
	elseif(!has_feeds()) {
	?>
		<div style="border:1px solid #e7dc2b;background: #fff888;margin:15px;padding:10px;"><?php printf(_r('You haven\'t added any feeds yet. Add them from <a href="%s">your admin panel</a>'), 'admin/'); ?></div>
	<?php
	}
	else {
	?>
		<div style="border:1px solid #e7dc2b;background: #fff888;margin:15px;padding:10px;">No items available in the last <?php echo get_offset(true) ?> hours. Try <a href="index.php?hours=-1" id="viewallitems">viewing all items.</a></div>
		<div style="border:1px solid #e7dc2b;background: #fff888;margin:15px;padding:10px;display:none;">Now loading all available items - If they don't load within 20 seconds, click <a href="index.php?hours=-1">here</a><br /><img src="<?php template_directory(); ?>/loading.gif" alt="<?php _e('Loading...') ?>" /></div>
	<?php
	}
	?>
		</div>
	</div>

	<?php
	if(has_feeds()) {
		?>
	<div id="sources">
		<h3><?php _e('Sources') ?></h3>
		<ul><?php list_feeds('format=<li><a href="%1$s">%3$s</a> <a href="%4$s" class="feed-link">(' . _r('Feed') . ")</a></li>\n"); ?>
		</ul>
		<div class="clearer">&nbsp;</div>
	</div>
	<?php
	}
	else {
		//Already handled above; if there are no feeds, then there should be no items...
	}
		?>
	<div id="footer">
	<p><?php printf(_r('%s is proudly powered by <a href="http://getlilina.org/">Lilina News Aggregator</a>'), get_option('sitename')); ?></p>
	<!-- <?php global $timer_start; echo lilina_timer_end($timer_start); ?> -->
	<?php template_footer(); ?>
	</div>

	<script language="JavaScript" type="text/javascript" src="<?php echo get_option('baseurl') ?>inc/js/jquery.js"></script>
	<script language="JavaScript" type="text/javascript" src="<?php echo get_option('baseurl') ?>inc/js/jquery.scrollTo.js"></script>
	<script language="JavaScript" type="text/javascript" src="<?php template_directory(); ?>/effects.js"></script>
</body>
</html>