<?php
/**
* OPML feeds export, different to the items export on rss.php
*
* @author Ryan McCue <cubegames@gmail.com>
* @package Lilina
* @version 1.0
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

function export_opml() {
	header('Content-Type: application/xml; charset=utf-8');
	echo '<?xml version="1.0" encoding="' . get_option('encoding', 'utf-8') . '"?'.'>';
?>

<opml version="1.1">
	<head>
		<title><?php echo get_option('sitename'); ?></title>
		<dateModified><?php /** This is unclean */ echo date('D, j M Y G:i:s O'); ?></dateModified>
	</head>
	<body>
<?php
if (has_feeds()) {
	foreach (get_feeds() as $feed) {
		?>
		<outline text="<?php echo htmlspecialchars($feed->name) ?>" title="<?php echo htmlspecialchars($feed->name) ?>" type="rss" xmlUrl="<?php echo htmlspecialchars($feed->feed) ?>" htmlUrl="<?php echo htmlspecialchars($feed->url) ?>" />
		<?php
	}
}
?>
	</body>
</opml>
<?php
}

Controller::registerMethod('opml', 'export_opml');