<?php
/**
 * Apple-style template for Lilina
 * @author Ryan McCue <cubegames@gmail.com>
 * @author Jon Hicks <jon@hicksdesign.co.uk>
 */
/**
*/
header('Content-Type: text/html; charset=utf-8');

require_once(LILINA_INCPATH . '/core/auth-functions.php');

//Authentication Section
if(isset($_POST['user']) && isset($_POST['pass'])) {
	$test = lilina_auth($_POST['user'], $_POST['pass']);
}

if(!isset($test) || $test == 'error') {
	$test = false;
}

if($test === true && isset($_GET['logout']) && $_GET['logout'] == 'logout') {
	lilina_logout();
	die();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/1">

	<title><?php /*the_page_title();*/ template_sitename();?></title>

	<link rel="stylesheet" type="text/css" href="<?php template_directory(); ?>/reset.css" media="screen"/>
	<link rel="stylesheet" type="text/css" href="<?php template_directory(); ?>/style.css" media="screen"/>
	<link rel="stylesheet" type="text/css" href="<?php template_directory(); ?>/content.css" media="screen"/>

	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />

<?php
	template_header();
?>

</head>
<body class="river-page">

<div id="logo">&nbsp;</div>

<div id="navigation">
  	<span class="site-name"><?php template_sitename() ?></span> |
	<a href="admin/"><?php echo ($test ? 'Admin Panel' : 'Log In' ); ?></a> |
	<a href="atom.php">Subscribe to Feed</a> |
	<a href="opml.php">OPML</a>
</div>

<div id="main">
	<div id="wrapper">
		<div id="content">
			<h1 id="page-title"><?php /*the_page_title();*/ ?>Hello</h1>
			<div id="content-quicklinks"><?php /* Nothing yet */ ?></div>
			<ul>
<?php
// We call it with false as a parameter to avoid incrementing the item number
if(has_items(false)) {
		while(has_items()): the_item();
?>
				<li class="feed-<?php the_feed_id(); ?> item" id="item-<?php the_id(); ?>">
					<div class="title-bar">
						<div class="main-section">
							<h3 class="title" id="title-<?php the_id(); ?>"><?php the_title(); ?></h3>
							&nbsp;
							<div class="excerpt"><p><?php the_summary(200) ?></p></div>
							<div class="read"><a href="<?php the_link(); ?>">Continue reading on the original site.</a></div>
						</div>
						<div class="secondary-section">
							<span class="feed"><?php the_feed_name() ?></span>
							<span class="date"><?php the_time('format=l d F, Y H:i'); ?></span>
						</div>
						<div class="clearer"></div>
					</div>
					<div class="content" id="content-<?php the_id(); ?>">
						<?php the_content(); ?>
					</div>
					<div class="action-bar">
						<?php action_bar('before=&after= | '); ?>
						<?php the_enclosure(); ?>
					</div>
				</li><?php
		endwhile;
}

elseif(!has_feeds()) {
?>
			<li>
				<h2>Whoops!</h2>
				<p>No feeds exist!</p>
			</li>
<?php
}
else {
?>
			<div style="border:1px solid #e7dc2b;background: #fff888;margin:15px;padding:10px;">No items available from in the last <?php echo get_offset(true); ?> hour(s). Try <a href="index.php?hours=-1" id="viewallitems">viewing all items</a></div>
			<div style="background: url('<?php template_directory(); ?>/spinner-back.png');margin:15px;padding:10px;display:none;">Now loading all available items - If they don't load within 20 seconds, click <a href="index.php?hours=-1">here</a><br /><img src="<?php template_directory(); ?>/spinner.gif" alt="Loading..." /></div>
<?php
}
?>
			</ul>
		</div>
	</div>
</div>


<div id="sidebar">
	<ul>
		<?php if( has_feeds() ): ?>
		<li id="sources"><h3>Sources</h3>
			<ul>
				<?php list_feeds('title_length=35&format=<li><a href="%1$s"><img class="icon" src="%2$s" /><span class="title">%3$s</span></a></li>'); ?>
			</ul>
		</li>
		<?php endif; ?>
		<li>Powered by <a href="http://getlilina.org/">Lilina News Aggregator</a></li>
	</ul>
</div>

<?php template_footer(); ?>
<!-- Generated in: <?php global $timer_start; echo lilina_timer_end($timer_start); ?> -->

<script src="<?php echo get_option('baseurl') ?>inc/js/jquery.js"></script>
<script src="<?php template_directory(); ?>/greader.js"></script>
</body>
</html>