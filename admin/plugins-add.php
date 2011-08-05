<?php
/**
 * Plugin installation page
 *
 * @package Lilina
 * @subpackage Admin
 */

require_once('admin.php');

$action = 'main';
if (!empty($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
}
switch ($action) {
	case 'update':
		die();

	/*case 'search':
		if (!empty($_REQUEST['name'])) {
			admin_header(_r('Search Results'), 'plugins.php');
			var_dump(Lilina_Updater_Plugins::search($_REQUEST['name']));
			admin_footer();
			die();
		}
		$message = _r('No name specified.');
		// pass-through

	case 'main':
		admin_header(_r('Plugin Installer'), 'plugins.php');
		if (!empty($message)) {
			echo '<div class="message"><p>' . $message . '</p></div>';
		}
?>
		<h1><?php _e('Install a Plugin'); ?></h1>
		<form action="" method="POST">
			<h2><?php _e('Search by Name') ?></h2>
			<div class="row">
				<label for="name"><?php _e('Plugin Name:') ?></label>
				<input name="name" type="text" id="name" />
			</div>
			<input type="hidden" name="action" value="search" />
			<p class="buttons"><button type="submit" class="positive"><?php _e('Search') ?></button></p>
		</form>
<?php
		break;*/
	default:
		admin_header(_r('Plugin Installer'), 'plugins.php');
?>
		<p><?php _e("There's nothing here&hellip;") ?></p>
<?php
}
		admin_footer();
?>