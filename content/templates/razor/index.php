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
	<?php
	template_header();
	?>
</head>
<body>
	<div id="header">

		<h1 id="title"><a href="<?php echo get_option('baseurl') ?>"><?php echo get_option('sitename') ?></a></h1>
		<p id="messagearea"></p>
		<ul id="menu">
			<li id="update"><a href="?method=update" title="Update your feeds">Update</a></li>
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
/*
$library = new LibraryView('library', 'Library');
$everything = new LibraryView('everything', 'Everything');
$library->add_child($everything);
*/
/*$everything->add_child(new LibraryView('most', 'Well, Most Things'));
$library->add_child(new LibraryView('unread', 'Unread'));*/
/*
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
}*/
?>
			<h2>Library</h2>
			<ul id="library">
				<li id="library-everything" class="selected"><a href="#library">Everything</a></li>
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
				<li><a id="footer-add" href="<?php echo get_option('baseurl') ?>admin/feeds.php#add">Add</a></li>
				<li><a href="<?php echo get_option('baseurl') ?>admin/feeds.php">Manage</a></li>
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

	<div id="items-list-container">
		<ol id="items-list">
			<li><a href="#">Loading items...</a></li>
		</ol>
		<div class="footer">
			<ul>
				<li><a id="items-reload" href="<?php echo get_option('baseurl') ?>">Reload</a></li>
			</ul>
		</div>
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
		<div class="footer">
			<ul>
			</ul>
		</div>
	</div>

	<?php template_footer(); ?>

	<script type="text/javascript" src="<?php echo get_option('baseurl') ?>inc/js/jquery.js"></script>
	<script type="text/javascript" src="<?php echo get_option('baseurl') ?>inc/js/api.js"></script>
	<script type="text/javascript" src="<?php template_directory() ?>/core.js"></script>
	<script>
		Razor.scriptURL = "<?php template_directory() ?>";
	</script>
</body>
</html>