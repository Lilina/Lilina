<?php
/**
 * Atom feed generator
 *
 * Generates a Atom feed from the available items.
 * Based on Wordpress' feed-atom.php
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
define('LILINA',1);
define('LILINA_PATH', dirname(__FILE__));
define('LILINA_INCPATH', LILINA_PATH . '/inc');
$nothing = array();
//Require our settings, must be first required file
require_once('./inc/core/conf.php');

//Require our standard stuff
require_once('./inc/core/lib.php');

//Plugins
require_once('./inc/core/plugin-functions.php');

//Stuff for parsing Magpie output, etc
require_once('./inc/core/feed-functions.php');

//File input and output
require_once('./inc/core/file-functions.php');

//Template files, needed for middle-man parsing
require_once('./inc/core/skin.php');
//header('Content-type: application/atom+xml; charset=' . $settings['encoding'], true);
echo '<?xml version="1.0" encoding="'.$settings['encoding'].'"?'.'>'; ?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:thr="http://purl.org/syndication/thread/1.0" xml:lang="<?php echo $settings['lang']; ?>" xml:base="<?php echo $settings['baseurl']; ?>atom.php"<?php call_hooked('atom_ns'); ?>>
	<title type="text"><?php echo $settings['sitename']; ?></title>

	<updated><?php //echo date('Y-m-d\TH:i:s\Z', last_item('timestamp')); ?></updated>
	<generator uri="http://lilina.cubegames.net/" version="<?php $lilina['core-sys']['version']; ?>">Lilina News Aggregator</generator>

	<link rel="alternate" type="text/html" href="<?php echo $settings['baseurl']; ?>" />
	<id><?php echo $settings['baseurl'], 'atom.php'; ?></id>
	<link rel="self" type="application/atom+xml" href="<?php echo $settings['baseurl']; ?>atom.php" />

	<?php call_hooked('atom_head', $nothing); ?>
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
<?php call_hooked('atom_entry', $item); ?>
	</entry>
	<?php
		}
	}
	?>
</feed>
