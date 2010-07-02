<?php
/**
 * Watchorn template for Lilina
 * @author Ryan McCue <cubegames@gmail.com>
 */

/**
 * Make sure we're UTF-8
 */
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/1">
	<title><?php template_sitename();?></title>
	<link rel="stylesheet" type="text/css" href="<?php template_file('style.css') ?>" media="screen"/>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<script language="JavaScript" type="text/javascript" src="<?php echo get_option('baseurl') ?>inc/js/jquery.js"></script>
	<script language="JavaScript" type="text/javascript" src="<?php template_directory() ?>/effects.js"></script>
	<?php
	template_header();
	?>
</head>
<body>
	<div id="header">
		<h1><a href="<?php template_siteurl() ?>" rel="home"><?php template_sitename() ?></a></h1>
	</div>

	<div id="main" class="hfeed">
		<ul id="items">
<?php

// We call it with false as a parameter to avoid incrementing the item number
if(has_items()) {
	while(has_items()): the_item();
?>
			<li class="hentry item" id="item-<?php the_id() ?>">
				<h2 class="item-title entry-title"><a href="<?php the_link() ?>" rel="bookmark"><?php the_title() ?></a></h2>
				<div class="item-date"><abbr class="published" title="<?php the_date('format=Y-m-d\TH:i:sO') ?>"><?php the_date('format=F j, Y') ?> &#8211; <?php the_date('format=g:i a') ?></abbr></div>
				<div class="item-content entry-content">
					<?php the_content() ?>
				</div>
				<div class="item-metadata">
					<div class="item-source author vcard"><?php printf(_r('By %s', 'watchorn'), '<a href="' . get_the_feed_url() . '" class="url fn n" >' . get_the_feed_name() . '</a>') ?></div>
					<?php if (has_enclosure()): ?>
					<span class="meta-sep">|</span>
					<div class="item-enclosure"><?php the_enclosure() ?></div>
					<?php endif; ?>
				</div> <!-- .item-metadata -->
				<?php action_bar(array(
					'header' => "<div class='item-actions'>\n\t\t\t\t\t<h3>" . _r('Actions', 'watchorn') . "</h3>\n\t\t\t\t\t<ul>\n\t\t\t\t\t\t",
					'footer' => "</ul>\n\t\t\t\t</div>\n",
					'before' => "<li class='action'>",
					'after' => "</li>\n\t\t\t\t\t\t"
				)) ?>
			</li> <!-- .item -->
<?php

	endwhile;
}
elseif(!has_feeds()) {
?>
			<li class="error">You haven't added any feeds yet. Add them from <a href="admin/">your admin panel</a></li>
<?php
}
else {
?>
			<li class="error">No items were found.</div>
<?php
}
?>
		</ul>
	</div> <!-- #main -->

<?php
if(has_feeds()):
?>
	<div id="sources">
		<h2><?php _e('Sources', 'watchorn') ?></h2>
		<ol class="xoxo">
			<?php list_feeds('format=<li class="source"><a href="%1$s" class="source-link">%3$s</a> <ul><li><a href="%4$s" class="source-feed">' . _r('Feed') . "</a></li></ul></li>\n\t\t"); ?>
		</ol>
	</div> <!-- #sources -->
<?php
endif;
?>

	<div id="footer">
		<span id="generator-link"><a href="http://getlilina.org/" title="Lilina" rel="generator">Lilina</a></span>
		<span class="meta-sep">|</span>
		<span id="theme-link"><a href="http://getlilina.org/docs/template:watchorn" title="<?php _e('Watchorn theme for Lilina', 'watchorn'); ?>" rel="designer"><?php _e('Watchorn', 'watchorn'); ?></a></span>
		<span class="meta-sep">|</span>
		<span id="admin-link"><a href="<?php echo get_option('baseurl') ?>admin/" title="<?php _e('Administration Panel', 'watchorn'); ?>" rel="administration"><?php _e('Admin', 'watchorn'); ?></a></span>
	</div> <!-- #footer -->

	<?php template_footer(); ?>
</body>
</html>