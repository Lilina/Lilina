<?php
/**
 * @todo Move to admin/settings.php
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

defined('LILINA') or die('Restricted access');
require_once(LILINA_INCPATH . '/core/file-functions.php');
?>
<h2><?php _e('Settings'); ?></h2>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
	<fieldset id="general">
		<legend><?php _e('General Settings'); ?></legend>
		<p class="option">
			<label for="sitename"><?php _e('Site name'); ?>:</label>
			<input type="text" name="sitename" id="sitename" value="<?php echo $settings['sitename']; ?>" />
		</p>
		<p class="option">
			<label for="baseurl"><?php _e('Site address (URL)'); ?>:</label>
			<input type="text" name="baseurl" id="baseurl" value="<?php echo $settings['baseurl']; ?>" />
		</p>
	</fieldset>
	<fieldset id="views">
		<legend><?php _e('Viewing Settings'); ?></legend>
		<p class="option">
			<label for="template"><?php _e('Template'); ?>:</label>
			<select id="template" name="template">
				<?php
				foreach(available_templates() as $template) {
					echo '<option value="', $template['name'];
					if($template['name'] === $settings['template']) {
						echo '" selected="selected';
					}
					echo '">', $template['real_name'], '</option>';
				}
				?>
			</select>
		</p>
		<p class="option">
			<label for="lang">Language</label>
			<select id="lang" name="lang">
				<?php
				echo '<option';
				if('en' == $settings['lang']) {
					echo ' selected="selected"';
				}
				echo ' value="en">English (default)</option>';
				foreach(available_locales() as $locale) {
					echo '<option';
					if($locale['name'] === $settings['lang']) {
						echo ' selected="selected"';
					}
					echo '>', $locale['name'], '</option>';
				}
				?>
			</select>
		</p>
	</fieldset>
	<input type="submit" value="<?php _e('Save Settings'); echo ' (Not yet functional)'; ?>" disabled="disabled" />
</form>
<h3>Troubleshooting</h3>
<a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=settings&amp;action=diagnostic">Run diagnostic test</a>
<h3>Reset</h3>
<p>This will delete your settings.php and you will need to run install.php again. <a href="<?php echo $_SERVER['PHP_SELF'];?>?page=settings&amp;action=reset">Proceed?</a></p>