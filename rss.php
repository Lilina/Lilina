<?php
/**
 * RSS 2.0 feed generator
 *
 * Generates a RS feed from the available items.
 * Based on Wordpress' feed-rss2.php
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @author WordPress Team
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * @todo Document
 */
define('LILINA',1);
define('LILINA_PATH', dirname(__FILE__));
define('LILINA_INCPATH', LILINA_PATH . '/inc');

//Check installed
require_once(LILINA_INCPATH . '/core/install-functions.php');
lilina_check_installed();

//Require our settings, must be first required file
require_once(LILINA_INCPATH . '/core/conf.php');
require_once(LILINA_INCPATH . '/core/plugin-functions.php');
require_once(LILINA_INCPATH . '/core/l10n.php');
require_once(LILINA_INCPATH . '/core/feed-functions.php');
require_once(LILINA_INCPATH . '/core/misc-functions.php');
require_once(LILINA_INCPATH . '/core/file-functions.php');
require_once(LILINA_INCPATH . '/core/skin.php');

header('Content-Type: text/xml; charset=' . get_option('encoding'), true);
global $lilina;
echo '<?xml version="1.0" encoding="'.get_option('encoding').'"?'.'>';
?>

<!-- generator="Lilina/<?php echo $lilina['core-sys']['version']; ?>" -->
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	<?php do_action('rss2_ns'); ?>
>

<channel>
	<title><?php echo get_option('sitename');?></title>
	<link><?php echo get_option('baseurl') ?></link>
	<description><?php echo get_option('sitename'), _r(', an online feed aggregator'); ?></description>
	<atom:link href="<?php echo get_option('baseurl'), 'rss.php'; ?>" rel="self" type="application/rss+xml" />
	<?php //Need to fix this ?>
	<pubDate><?php echo date('D, d M Y H:i:s +0000'); ?></pubDate>
	<generator>http://getlilina.org/?v=<?php echo $lilina['core-sys']['version']; ?></generator>
	<language><?php echo get_option('lang'); ?></language>
	<?php do_action('rss2_head');
	if(has_items(false)) {
		while(has_items()) { the_item();
	?>
	<item>
		<title><![CDATA[<?php the_title(); ?>]]></title>
		<link><?php the_link(); ?></link>
		<pubDate><?php the_date('format=D, d M Y H:i:s +0000'); ?></pubDate>
		<?php //Not entirely accurate; uses the feed name, not the author ?>
		<dc:creator><?php the_feed_name(); ?></dc:creator>

		<guid isPermaLink="false"><?php the_id(); ?></guid>
		<description><![CDATA[<?php the_summary(); ?>]]></description>
		<content:encoded><![CDATA[<?php the_content(); ?>]]></content:encoded>
		<?php do_action('rss2_item'); ?>
	</item>
	<?php }
	}
	?>
</channel>
</rss>