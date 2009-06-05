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


/**
 * Activate a plugin
 *
 * @since 1.0
 *
 * @param string $plugin_file Relative path to plugin
 * @return bool Whether plugin was activated
 */
function activate_plugin($plugin_file) {
	global $current_plugins;
	$plugin_file = trim($plugin_file);

	if(!validate_plugin($plugin_file))
		return false;
	$current_plugins[md5($plugin_file)] = $plugin_file;
	
	$data = new DataHandler();
	$data->save('plugins.data', serialize($current_plugins));
	return true;
}

/**
 * Deactivate a plugin
 *
 * @since 1.0
 *
 * @param string $plugin_file Relative path to plugin
 * @return bool Whether plugin was deactivated
 */
function deactivate_plugin($plugin_file) {
	global $current_plugins;
	
	if(!isset($current_plugins[md5($plugin_file)]))
		return false;

	if(!validate_plugin($plugin_file))
		return false;

	unset($current_plugins[md5($plugin_file)]);
	
	$data = new DataHandler();
	$data->save('plugins.data', serialize($current_plugins));
	return true;
}

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
	/** Needs better validation */
	if(!empty($_POST['sitename']))
		update_option('sitename', $_REQUEST['sitename']);
	if(!empty($_POST['template']))
		update_option('template', $_REQUEST['template']);
	if(!empty($_POST['locale']))
		update_option('locale', $_REQUEST['locale']);
	if(!empty($_POST['timezone']))
		update_option('timezone', $_REQUEST['timezone']);

	header('HTTP/1.1 302 Found', true, 302);
	header('Location: ' . get_option('baseurl') . 'admin/settings.php?updated=1');
	die();
}

require_once(LILINA_INCPATH . '/core/file-functions.php');
admin_header(_r('Settings'));
?>
<h1><?php _e('Settings'); ?></h1>
<?php
if(!empty($_GET['updated']))
	echo '<div id="message"><p>' . _r('Settings updated!') . '</p></div>';
?>
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
	<input type="hidden" name="action" value="settings" />
	<input type="hidden" name="_nonce" value="<?php echo generate_nonce() ?>" />
	<input type="submit" value="<?php _e('Save') ?>" class="submit" />
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
					<td><a href="settings.php?deactivate_plugin=<?php echo $plugin_file ?>"><?php  _e('Dectivate') ?></a></td>
<?php
else:
?>
					<td><a href="settings.php?activate_plugin=<?php echo $plugin_file ?>"><?php  _e('Activate') ?></a></td>
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