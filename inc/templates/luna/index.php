<?php
/**
 * The Luna template for Lilina
 *
 * Based on the 'Fancy' template for Planet
 * @author Planet Development Team <http://planetplanet.org/>
 */
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title><?php template_sitename();?></title>
	<link rel="stylesheet" href="<?php template_directory() ?>/style.css" type="text/css">
	<?php
	template_header();
	?>
</head>

<body>
<h1><?php template_sitename();?></h1>

<?php
$notfirst = false;
while(has_items()):
	the_item();

	if(!date_equals('F d, Y')) {
		if($notfirst) {
?>
			<!-- End <div class="channelgroup"> -->
			</div>
			<!-- End <div class="daygroup"> -->
			</div>
<?php
		}
?>
		<div class="daygroup">
		<h2><?php the_date('format=F d, Y'); ?></h2>
<?php
	}

	if(!feed_equals()) {
		if($notfirst) {
?>
			<!-- End <div class="channelgroup"> -->
			</div>
<?php
		}
?>
		<div class="channelgroup">
			<h3><a href="<?php the_feed_url() ?>"><?php the_feed_name() ?></a></h3>
<?php
		/**
		 * Feed faces ("hackergotchi") are disabled, because of the lack of
		 * usefulness. Instead, we'll offer a plugin hook here.
		 */
		do_action('channelgroup');
	}
?>
	<div class="entrygroup" id="<?php the_id() ?>"></div>
	<h4><a href="<?php the_link() ?>"><?php the_title() ?></a></h4>
	<div class="entry">
	<div class="content">
		<?php the_content() ?>
	</div>

<p class="date">
<a href="<?php the_link() ?>"><TMPL_IF author>by <TMPL_VAR author> at </TMPL_IF><?php the_date() ?><TMPL_IF category> under <TMPL_VAR category></TMPL_IF></a>
</p>
</div>
</div>
<?php
	$notfirst = true;
endwhile;
?>
<!-- End <div class="channelgroup"> -->
</div>
<!-- End <div class="daygroup"> -->
</div>

<div class="sidebar">
<img src="images/logo.png" width="136" height="136" alt="">

<h2>Subscriptions</h2>
<ul>
<?php
list_feeds('format=<li>
<a href="%4$s" title="subscribe"><img src="' . get_template_directory() . '/images/feed.png" alt="(feed)"></a> <a href="%1$s">%3$s</a>
</li>');
?>
</ul>

<p>
<strong>Last Updated:</strong><br />
<?php echo date('F d y h:m:s A') ?><br />
<em>All times are UTC.</em><br />
<br />
Powered by <a href="http://getlilina.org/">Lilina</a>.
</p>

<?php do_action('theme_sidebar'); ?>
</div>
</body>

</html>