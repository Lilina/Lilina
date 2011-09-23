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

admin_header(_r('Welcome!'));

/**
 * Make a timestamp into a relative string
 *
 * @todo Tidy up and move out of this file.
 * Based on Garrett Murray's code from http://graveyard.maniacalrage.net/etc/relative/
 */
function relative_time($posted_date) {
	$in_seconds = $posted_date;
	$diff = time()-$in_seconds;
	$months = floor($diff/2592000);
	$diff -= $months*2419200;
	$weeks = floor($diff/604800);
	$diff -= $weeks*604800;
	$days = floor($diff/86400);
	$diff -= $days*86400;
	$hours = floor($diff/3600);
	$diff -= $hours*3600;
	$minutes = floor($diff/60);
	$diff -= $minutes*60;
	$seconds = $diff;
 
	if ($months > 0) {
		return sprintf(_c('on %s', 'on <date>'), date('l, jS \o\f F, Y'));
	}

	switch (true) {
		case $weeks > 0:
			// weeks and days
			$week = sprintf(Localise::ngettext('%d week', '%d weeks', $weeks), $weeks);
			if ($days > 0) {
				$day = sprintf(Localise::ngettext('%d day', '%d days', $days), $days);
				$relative_date = sprintf(_c('%s, %s ago', 'relative time, "x weeks, x days ago"'), $week, $day);
			}
			else {
				$relative_date = sprintf(_c('%s ago', 'relative time, "x weeks ago"'), $week);
			}
			break;
		case $days > 0:
			// days and hours
			$day = sprintf(Localise::ngettext('%d day', '%d days', $days), $days);
			if ($hours > 0) {
				$hour = sprintf(Localise::ngettext('%d hour', '%d hours', $hours), $hours);
				$relative_date = sprintf(_c('%s, %s ago', 'relative time, "x days, x hours ago"'), $day, $hour);
			}
			else {
				$relative_date = sprintf(_c('%s ago', 'relative time, "x days ago"'), $day);
			}
			break;
		case $hours > 0:
			// hours and minutes
			$hour = sprintf(Localise::ngettext('%d hour', '%d hours', $hours), $hours);
			if ($minutes > 0) {
				$minute = sprintf(Localise::ngettext('%d minute', '%d minutes', $minutes), $minutes);
				$relative_date = sprintf(_c('%s, %s ago', 'relative time, "x hours, x minutes ago"'), $hour, $minute);
			}
			else {
				$relative_date = sprintf(_c('%s ago', 'relative time, "x hours ago"'), $hour);
			}
			break;
		case $minutes > 0:
			// minutes only
			return sprintf(Localise::ngettext('%d minute ago', '%d minutes ago', $minutes), $minutes);
			break;
		case $seconds > 0:
			// seconds only
			return sprintf(Localise::ngettext('%d second ago', '%d seconds ago', $seconds), $seconds);
			break;
	}
	return $relative_date;
}
?>
<h1><?php _e('Welcome!') ?></h1>
<?php
if (count(Feeds::get_instance()->getAll()) === 0) {
?>
<p><?php _e("Firstly, thanks for using Lilina! To help you settle in, we've included a few nifty tools in Lilina, just to help you get started.") ?></p>
<?php
}
else {
	$updated = get_option('last_updated');
	if (!$updated) {
		$message = sprintf(_r('You currently have %d items in %d feeds. Never updated.', count(Items::get_instance()->get_items()), count(Feeds::get_instance()->getAll())));
	}
	else {
		$message = sprintf(_r('You currently have %d items in %d feeds. Last updated %s.'), count(Items::get_instance()->get_items()), count(Feeds::get_instance()->getAll()), relative_time($updated));
	}
?>
<p><?php echo $message ?></p>
<?php
}
?>
<h2><?php _e('Import') ?></h2>
<p><?php _e("We can import from any service which supports an open standard called OPML. Here's some services you can import from:") ?></p>
<ul id="block-list">
	<li class="greader"><a href="feed-import.php?service=greader"><?php _e('Google Reader') ?></a></li>
	<li class="gregarius"><a href="feed-import.php?service=gregarius"><?php _e('Gregarius') ?></a></li>
	<li class="other"><a href="feed-import.php?service=opml"><?php _e('OPML (desktop readers)') ?></a></li>
	<li class="other"><a href="feed-import.php"><?php _e('Others') ?></a></li>
</ul>
<p class="sidenote"><?php echo sprintf(_r('Looking to import from another service? Try our <a href="%s">open documentation</a> to see what other users have found.'), 'http://getlilina.org/wiki/importing') ?></p>
<h2><?php _e('Quick Adding') ?></h2>
<p><?php _e('Use this bookmarlet to subscribe to feeds straight from your browser:')?><br />
<a href="javascript:void(sp=window.open('<?php echo get_option('baseurl') ?>admin/subscribe.php?url='+escape(document.location),'lilina','toolbar=no,resizable=no,width=450,height=430,scrollbars=yes'));%20void(setTimeout(function(){sp.focus()},100));" class="bookmarklet"><?php _e('Subscribe') ?></a></p>
<h2><?php _e('Updating Your Feeds') ?></h2>
<p><?php _e('Lilina offers several ways to update your feeds. Some templates offer an update button, while others leave it to you to work out.') ?></p>
<p><?php printf(_r('To update your feeds from your browser, simply access <a href="%1$s">the updater</a> in your browser. You can also access this URL via cron, by appending <code>&amp;cron</code>.'), get_option('baseurl') . '?method=update') ?></p>
<p><?php printf(_r('For more information on updating, see the <a href="%s">documentation</a>.'), 'http://codex.getlilina.org/wiki/Updating_Feeds') ?></p>
<?php
admin_footer();