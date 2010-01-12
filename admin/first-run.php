<?php
/**
 * First run tools, such as the importer, on a single page
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @subpackage Administration
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/** */
require_once('admin.php');

admin_header(_r('First-Run'));
?>
<h1><?php _e('Welcome') ?></h1>
<p><?php _e("To help you settle in, we've included a few nifty tools in Lilina, just to help you get started.") ?></p>
<h2><?php _e('Import') ?></h2>
<p><?php _e("We can import from any service which supports an open standard called OPML. Here's some services you can import from:") ?></p>
<ul id="block-list">
	<li class="greader"><a href="feed-import.php?service=greader"><?php _e('Google Reader') ?></a></li>
	<li class="bloglines"><a href="feed-import.php?service=bloglines"><?php _e('Bloglines') ?></a></li>
	<li class="other"><a href="feed-import.php?service=opml"><?php _e('OPML (desktop readers)') ?></a></li>
	<li class="other"><a href="feed-import.php"><?php _e('Others') ?></a></li>
</ul>
<p class="sidenote"><?php echo sprintf(_r('Looking to import from another service? Try our <a href="%s">open documentation</a> to see what other users have found.'), 'http://getlilina.org/wiki/importing') ?></p>
<h2><?php _e('Quick Adding') ?></h2>
<p><?php _e('Use this bookmarlet to subscribe to feeds straight from your browser:')?> 
<a href="javascript:void(sp=window.open('<?php echo get_option('baseurl') ?>admin/subscribe.php?url='+escape(document.location),'lilina','toolbar=no,resizable=no,width=450,height=430,scrollbars=yes'));%20void(setTimeout(function(){sp.focus()},100));"><?php _e('Subscribe') ?></a></p>
<?php
admin_footer();