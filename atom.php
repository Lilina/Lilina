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
define('LILINA',1);
define('LILINA_PATH', dirname(__FILE__));
define('LILINA_INCPATH', LILINA_PATH . '/inc');
//Require our settings, must be first required file
require_once(LILINA_INCPATH . '/core/conf.php');

//Require our standard stuff
require_once(LILINA_INCPATH . '/core/lib.php');

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
	<id><?php echo get_option('baseurl'), 'atom.php'; ?></id>
	<link rel="self" type="application/atom+xml" href="<?php echo get_option('baseurl'); ?>atom.php" />

	<?php do_action('atom_head'); ?>
	<?php 
	if(has_items()) {
		foreach(get_items() as $item) {
	?>
	<entry>
		<author>
			<name><?php echo $item['channel_title']; ?></name>
			<uri><?php echo $item['channel_link']; ?></uri>
		</author>
		<title type="html"><![CDATA[<?php echo $item['title']; ?>]]></title>
		<link rel="alternate" type="text/html" href="<?php echo $item['link']; ?>" />
		<id><?php echo $item['guid']; ?></id>
		<updated><?php echo date('Y-m-d\TH:i:s\Z', $item['timestamp']); ?></updated>
		<published><?php echo date('Y-m-d\TH:i:s\Z', $item['timestamp']); ?></published>
		<summary type="html"><![CDATA[<?php echo $item['summary']; ?>]]></summary>
		<content type="html" xml:base="<?php echo $item['link']; ?>"><![CDATA[<?php echo $item['summary']; ?>]]></content>
<?php //atom_enclosure(); ?>
<?php do_action('atom_entry'); ?>
	</entry>
	<?php
		}
	}
	?>
</feed>
