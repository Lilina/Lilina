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
	<script type="text/javascript" src="<?php echo get_option('baseurl') ?>inc/js/jquery.js"></script>
	<script type="text/javascript" src="<?php echo get_option('baseurl') ?>inc/js/api.js"></script>
	<script type="text/javascript" src="<?php template_directory() ?>/date.extensions.js"></script>
	<script type="text/javascript" src="<?php template_directory() ?>/to_relative_time.jquery.js"></script>
	<script type="text/javascript" src="<?php template_directory() ?>/core.js"></script>
	<script type="text/javascript" src="<?php template_directory() ?>/ui.js"></script>
</head>
<body>
	<div id="header">

		<h1><a href="#"><?php echo get_option('sitename') ?></a></h1>
		<ul id="menu">
<?php
if($authenticated) {
?>
			<li><a href="<?php echo get_option('baseurl') ?>admin/settings.php">Settings</a></li>
			<li><a href="#help">Help</a></li>
			<li><a href="<?php echo get_option('baseurl') ?>admin/login.php?logout=logout&return=index.php">Logout</a></li>
<?php
}
else {
?>
			<li><a href="<?php echo get_option('baseurl') ?>admin/login.php?return=index.php">Login</a></li>
<?php
}
?>
		</ul>
	</div>

	<div id="sidebar">
		<div class="item-list">
			<!--<h2>Library</h2>
			<ul id="library-list">
				<li><a href="#everything">Everything</a></li>
				<li><a href="#unread">Unread</a></li>
			</ul>-->

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
				<li><a href="<?php echo get_option('baseurl') ?>admin/feeds.php#add">Add feed</a></li>
<?php
}
?>
			</ul>
		</div>
	</div>

	<div id="items-list">
		<ol>
			<li><a href="#">Loading items...</a></li>
		</ol>
	</div>

	<div id="item-view">
		<div id="item">
			<div id="heading">
				<h2 class="item-title">Example Item</h2>
				<p class="item-meta"><span class="item-source">From <a href="#external" class="external">Example Feed</a></span>. <span class="item-date">Posted <abbr class="relative" title="Sat, 01 Jan 2009 12:00:00">Sat, 01 Jan 2009 12:00:00</abbr></p>

			</div>
			<div id="item-content">
				<p>Lorem ipsum dolor...</p>
			</div>
		</div>
	</div>
</body>
</html>