<?php
/**
 * The Razor template for Lilina
 *
 * A 3-column layout, designed to look like a desktop application
 * @author Ryan McCue <http://ryanmccue.info/>
 */
header('Content-Type: text/html; charset=utf-8');

$user = new User();
$authenticated = !!$user->identify();
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo get_option('sitename') ?></title>
	<link rel="stylesheet" type="text/css" href="<?php template_directory() ?>/style.css" />
	<link rel="stylesheet" type="text/css" href="<?php template_directory() ?>/resources/fancybox/fancybox.css" />
	<script type="text/javascript" src="<?php echo get_option('baseurl') ?>inc/js/jquery.js"></script>
	<script type="text/javascript" src="<?php template_directory() ?>/resources/raphael-min.js"></script>
	<script type="text/javascript" src="<?php template_directory() ?>/resources/icons.js"></script>
	
	<script type="text/javascript" src="<?php echo get_option('baseurl') ?>inc/js/api.js"></script>
	<script type="text/javascript" src="<?php template_directory() ?>/date.extensions.js"></script>
	<script type="text/javascript" src="<?php template_directory() ?>/to_relative_time.jquery.js"></script>
	<script type="text/javascript" src="<?php template_directory() ?>/resources/fancybox/fancybox.js"></script>
	<script type="text/javascript" src="<?php template_directory() ?>/core.js"></script>
	<?php
	template_header();
	?>
</head>
<body>
	<div id="header">

		<h1><a href="<?php echo get_option('baseurl') ?>"><?php echo get_option('sitename') ?></a></h1>
		<ul id="menu">
			<li id="update"><a href="?method=update" title="Update your feeds">Update</a></li>
			<li id="updating">Now updating&hellip; <span class="progress"></span></li>
			<li id="help"><a href="#help" title="Learn how to use Razor">Help</a></li>
<?php
if($authenticated) {
?>
			<li id="settings"><a href="<?php echo get_option('baseurl') ?>admin/settings.php" title="Change your settings">Settings</a></li>
			<li id="logout"><a href="<?php echo get_option('baseurl') ?>admin/login.php?logout=logout&return=index.php" title="Log out of Lilina">Log out</a></li>
<?php
}
else {
?>
			<li id="login"><a href="<?php echo get_option('baseurl') ?>admin/login.php?return=index.php">Log in</a></li>
<?php
}
?>
		</ul>
	</div>

	<div id="sidebar">
		<div class="item-list">
<?php

$library = new LibraryView('library', 'Library');
$everything = new LibraryView('everything', 'Everything');
$everything->add_child(new LibraryView('most', 'Well, Most Things'));
$library->add_child($everything);
$library->add_child(new LibraryView('unread', 'Unread'));

$menu = array(
	'library' => $library
);
foreach ($menu as $id => $item) {
?>
	<h2><?php echo $item->get_title() ?></h2>
	<ul id="<?php echo $item->id ?>">
<?php
	foreach ($item->get_children() as $child) {
		echo print_library_item($child, $id);
	}
?>
	</ul>
<?php
}
?>
			<h2>Other</h2>
			<ul>
				<li class="expandable"><a href="#"><span class="arrow">&#x25B6;</span><span class="text">Some Folder</span></a></li>
				<li><a href="#"><img src="http://images.betanews.com/betanews2/icon_feed.png" />Some Folder</a></li>
			</ul>
			<h2>Feeds</h2>
			<ul id="feeds-list">
				<li><a href="#">Loading feeds...</a></li>
			</ul>
		</div>
		<div class="footer">

			<ul>
<?php
if($authenticated) {
?>
				<li><a id="footer-add" href="<?php echo get_option('baseurl') ?>admin/feeds.php#add">Add feed</a></li>
<?php
}
?>
			</ul>
		</div>
	</div>
	
	<div id="switcher" class="footer">
		<ul>
			<li><a href="#" id="switcher-sidebar">Sidebar</a></li>
			<li><a href="#" id="switcher-items">Items</a></li>
		</ul>
	</div>

	<div id="items-list">
		<ol>
			<li><a href="#">Loading items...</a></li>
		</ol>
	</div>

	<div id="item-view">
		<div id="item">
			<div id="heading">
				<h2 class="item-title">Welcome to Razor!</h2>
				<p class="item-meta"><span class="item-source">From <a href="#external" class="external">Example Feed</a></span>. <span class="item-date">Posted <abbr class="relative" title="Sat, 01 Jan 2009 12:00:00">Sat, 01 Jan 2009 12:00:00</abbr></p>

			</div>
			<div id="item-content">
				<p>	Razor is a template for Lilina, built to feel and act like
					a desktop feed reader.</p>
			</div>
		</div>
	</div>
	<?php template_footer(); ?>
</body>
</html>