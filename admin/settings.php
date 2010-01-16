<?php
/**
 * @todo Move to admin/settings.php
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/** */
require_once('admin.php');
require_once(LILINA_PATH . '/admin/includes/settings.php');
do_action('register_options');

if(isset($_REQUEST['activate_plugin'])) {
	activate_plugin($_REQUEST['activate_plugin']);
}
elseif(isset($_REQUEST['deactivate_plugin'])) {
	deactivate_plugin($_REQUEST['deactivate_plugin']);
}


if(!empty($_POST['action']) && $_POST['action'] == 'settings' && !empty($_POST['_nonce'])) {
	if(!check_nonce($_POST['_nonce']))
		lilina_nice_die('Nonces do not match.');
	clear_html_cache();

	$updatable_options = AdminOptions::instance()->whitelisted;
	foreach($updatable_options as $option) {
		if(!empty($_POST[$option])) {
			$value = apply_filters('options-sanitize-' . $option, $_POST[$option]);
			update_option($option, $value);
		}
	}

	header('HTTP/1.1 302 Found', true, 302);
	header('Location: ' . get_option('baseurl') . 'admin/settings.php?updated=1');
	die();
}

require_once(LILINA_INCPATH . '/core/file-functions.php');
admin_header(_r('Settings'));

if(!empty($_GET['updated']))
	echo '<div id="message" class="message"><p>' . _r('Settings updated!') . '</p></div>';
?>

<h1><?php _e('Settings'); ?></h1>
<form action="settings.php" method="post">
	<fieldset id="general">
		<legend><?php _e('General Settings'); ?></legend>
		<div class="row">
			<label for="sitename"><?php _e('Site name'); ?>:</label>
			<input type="text" name="sitename" id="sitename" value="<?php echo get_option('sitename'); ?>" />
		</div>
		<div class="row">
			<label for="baseurl"><?php _e('Site address (URL)'); ?>:</label>
			<input type="text" name="baseurl" id="baseurl" value="<?php echo get_option('baseurl'); ?>" disabled="disabled" />
			<p class="sidenote"><?php _e('This option must be changed in content/system/config/settings.php manually.'); ?></p>
		</div>
	</fieldset>
	<fieldset id="views">
		<legend><?php _e('Viewing Settings'); ?></legend>
		<div class="row">
			<label for="template"><?php _e('Template'); ?>:</label>
			<select id="template" name="template">
				<?php
				foreach(available_templates() as $template) {
					echo '<option value="', $template['name'];
					if($template['name'] === get_option('template')) {
						echo '" selected="selected';
					}
					echo '">', $template['real_name'], '</option>';
				}
				?>
			</select>
		</div>
		<div class="row">
			<label for="locale"><?php _e('Language') ?></label>
			<select id="locale" name="locale">
				<?php
				foreach(available_locales() as $locale) {
					echo '<option';
					if($locale['realname'] === get_option('locale')) {
						echo ' selected="selected"';
					}
					echo ' value="' . $locale['realname'] . '">', $locale['name'], '</option>';
				}
				?>
			</select>
		</div>
		<div class="row">
			<label for="timezone"><?php _e('Timezone'); ?>:</label>
			<select id="timezone" name="timezone">
				<?php
				foreach(timezone_identifiers_list() as $tz) {
					echo '<option';
					if($tz === get_option('timezone')) {
						echo ' selected="selected"';
					}
					echo '>', $tz, '</option>';
				}
				?>
			</select>
		</div>
	</fieldset>
	<fieldset id="update">
		<legend><?php _e('Updating Settings'); ?></legend>
		<div class="row">
			<label for="sitename"><?php _e('Update on'); ?>:</label>
			<select id="updateon" name="updateon">
				<option <?php if(get_option('updateon') == 'pageview') { echo 'selected="selected" '; } ?>value="pageview"><?php _e('Page View') ?></option>
				<option <?php if(get_option('updateon') == 'manual') { echo 'selected="selected" '; } ?>value="manual"><?php _e('Manual') ?></option>
			</select>
		</div>
		
		<h2><?php _e('Manual Updating') ?></h2>
		<p><?php printf(_r('The URL to manually update the items is <code>%s</code>'), get_option('baseurl') . '?method=update') ?></p>
		<p><?php _e('This URL will work regardless of the above option. If the "manual" option is selected, however, this is the only way to update the items.') ?></p>
		<p><?php printf(_r('For information on using cron with this URL, see the <a href="%s">documentation</a>.'), 'http://codex.getlilina.org/wiki/Updating_Feeds') ?></p>
	</fieldset>
	<?php do_action('options-form'); ?>
	<?php AdminOptions::instance()->do_sections(); ?>
	<input type="hidden" name="action" value="settings" />
	<input type="hidden" name="_nonce" value="<?php echo generate_nonce() ?>" />
	<p class="buttons"><button type="submit" class="positive"><?php _e('Save') ?></button></p>
</form>
<form action="settings.php" method="post">
	<fieldset id="plugins">
		<legend><?php _e('Plugin Management'); ?></legend>
		<table class="item-table">
			<thead>
				<tr>
					<th scope="col"><?php _e('Plugin') ?></th>
					<th scope="col"><?php _e('Version') ?></th>
					<th scope="col"><?php _e('Description') ?></th>
					<th scope="col"><?php _e('Status') ?></th>
					<th scope="col"><?php _e('Action') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
foreach(lilina_plugins_list(get_plugin_dir()) as $plugin):
	global $current_plugins;
	$plugin_meta = plugins_meta($plugin);
	$plugin_file = str_replace(get_plugin_dir(), '', $plugin);
?>
				<tr>
					<td><?php echo $plugin_meta->name ?></td>
					<td><?php echo $plugin_meta->version ?></td>
					<td><?php echo $plugin_meta->description ?></td>
					<td><?php if(isset($current_plugins[md5($plugin_file)])) _e('Activated'); else _e('Deactivated'); ?></td>
<?php
if( isset($current_plugins[md5($plugin_file)]) ):
?>
					<td><a href="settings.php?deactivate_plugin=<?php echo $plugin_file ?>" class="button negative"><?php  _e('Dectivate') ?></a></td>
<?php
else:
?>
					<td><a href="settings.php?activate_plugin=<?php echo $plugin_file ?>" class="button positive"><?php  _e('Activate') ?></a></td>
<?php
endif;
?>
				</tr>
<?php
endforeach;
?>
			</tbody>
		</table>
	</fieldset>
</form>
<?php
admin_footer();
?>