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
		try {
			if (empty($_REQUEST['plugin'])) {
				throw new Exception(_r('Plugin ID not specified'));
			}
			$new = Lilina_Updater_Plugins::check($_REQUEST['plugin']);
			if ($new === false) {
				throw new Exception(sprintf(_r('%s is up-to-date already.'), 'Plugin'));
			}

			Lilina_Updater_Plugins::update($_REQUEST['plugin']);

			header('HTTP/1.1 302 Found', true, 302);
			header('Location: ' . get_option('baseurl') . 'admin/plugins.php?updated=' . $_REQUEST['plugin']);
			die();
		}
		catch (Exception $e) {
			admin_header(_r('Update Plugin'), 'plugins.php');
?>
			<h1><?php _e('Whoops!') ?></h1>
			<p><?php echo $e->getMessage() ?></p>
<?php
			admin_footer();
			die();
		}

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