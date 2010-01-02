<?php
/**
 * Feeds page
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
require_once('admin.php');
require_once(LILINA_PATH . '/admin/includes/feeds.php');

admin_header(_r('Feeds'));

if(!empty($error))
	echo '<div id="alert" class="fade"><p>' . $error . '</p></div>';
if(!empty($message))
	echo '<div id="message"><p>' . $message . '</p></div>';
?>
<h1><?php _e('Feeds'); ?></h1>
<h2><?php _e('Current Feeds'); ?></h2>
<p><?php _e('Double-click the name or URL to edit.'); ?></p>
<table id="feeds_list" class="item-table">
	<thead>
		<tr>
		<th><?php _e('Feed Name'); ?></th>
		<th><?php _e('URL'); ?></th>
		<!--<th><?php _e('Category'); ?></th>-->
		<?php do_action('admin-feeds-infocol-description'); ?>
		<!--<th class="change-col"><?php _e('Edit Feed'); ?></th>-->
		<th class="remove-col"><?php _e('Remove Feed'); ?></th>
		<?php do_action('admin-feeds-actioncol-description'); ?>
		</tr>
	</thead>
	<tbody>
		<tr class="nojs"><td colspan="3"><?php _e('Javascript must be enabled.') ?></td></tr>
		<tr id="nofeeds"><td colspan="3"><?php _e("You don't have any feeds yet! Try adding some.") ?></td></tr>
	</tbody>
</table>
<form action="feeds.php" method="get" id="add_form">
	<h2><?php _e('Add Feed'); ?></h2>
	<fieldset id="required">
		<div class="row">
			<label for="add_url"><?php _e('Feed address (URL)'); ?>:</label>
			<input type="text" name="add_url" id="add_url" />
			<p class="sidenote"><?php _e('Example'); ?>: http://feeds.feedburner.com/lilina-news, http://getlilina.org</p>
		</div>
	</fieldset>
	<fieldset id="advanced" class="optional">
		<div class="row">
			<label for="add_name"><?php _e('Name'); ?>:</label>
			<input type="text" name="add_name" id="add_name" />
			<p class="sidenote"><?php _e('If no name is specified, it will be taken from the feed'); ?></p>
		</div>
	</fieldset>
	
	<input type="hidden" name="action" value="add" />
	<p class="buttons"><button type="submit" class="positive"><?php _e('Add'); ?></button></p>
</form>
<?php
admin_footer();
?>
