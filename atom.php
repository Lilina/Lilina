<?php
/**
 * Atom feed generator
 *
 * Generates a Atom feed from the available items.
 * Based on Wordpress' feed-atom.php
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
define('LILINA_PATH', dirname(__FILE__));
define('LILINA_INCPATH', LILINA_PATH . '/inc');

//Check installed
require_once(LILINA_INCPATH . '/core/install-functions.php');
lilina_check_installed();

//Require our settings, must be first required file
require_once(LILINA_INCPATH . '/core/conf.php');

//Plugins
require_once(LILINA_INCPATH . '/core/plugin-functions.php');
require_once(LILINA_INCPATH . '/core/misc-functions.php');

require_once(LILINA_INCPATH . '/core/l10n.php');

//Stuff for parsing Magpie output, etc
require_once(LILINA_INCPATH . '/core/feed-functions.php');

//File input and output
require_once(LILINA_INCPATH . '/core/file-functions.php');

//Template files, needed for middle-man parsing
require_once(LILINA_INCPATH . '/core/skin.php');
require_once(LILINA_INCPATH . '/core/version.php');
global $lilina;

header('Content-type: application/atom+xml; charset=' . get_option('encoding'), true);
echo '<?xml version="1.0" encoding="', get_option('encoding'), '"?'.'>'; ?>
<feed xmlns="http://www.w3.org/2005/Atom"
	xmlns:thr="http://purl.org/syndication/thread/1.0"
	xml:lang="<?php echo get_option('lang'); ?>"
	xml:base="<?php echo get_option('baseurl'); ?>atom.php"
	<?php do_action('atom_ns'); ?>>
	<title type="text"><?php echo get_option('sitename'); ?></title>

	<?php //Need to fix this ?>
	<updated><?php echo date('Y-m-d\TH:i:s\Z'); ?></updated>
	<generator uri="http://getlilina.org/" version="<?php echo $lilina['core-sys']['version']; ?>">Lilina News Aggregator</generator>

	<link rel="alternate" type="text/html" href="<?php echo get_option('baseurl'); ?>" />
	<id><?php echo get_option('baseurl');?>atom.php</id>
	<link rel="self" type="application/atom+xml" href="<?php echo get_option('baseurl'); ?>atom.php" />

	<?php do_action('atom_head'); ?>
	<?php 
	if(has_items(false)) {
		while(has_items()): the_item();
	?>
	<entry>
		<author>
			<name><?php the_feed_name(); ?></name>
			<uri><?php the_feed_url(); ?></uri>
		</author>
		<title type="html"><![CDATA[<?php the_title(); ?>]]></title>
		<link rel="alternate" type="text/html" href="<?php the_link(); ?>" />
		<id><?php the_id(); ?></id>
		<updated><?php the_date('Y-m-d\TH:i:s\Z'); ?></updated>
		<published><?php the_date('Y-m-d\TH:i:s\Z'); ?></published>
		<summary type="html"><![CDATA[<?php the_summary(); ?>]]></summary>
		<content type="html" xml:base="<?php the_link(); ?>"><![CDATA[<?php the_summary(); ?>]]></content>
		<?php atom_enclosure(); ?>
		<?php do_action('atom_entry'); ?>
	</entry>
	<?php
		endwhile;
	}
	?>
</feed>
