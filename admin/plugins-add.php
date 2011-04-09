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
	case 'search':
		if (!empty($_REQUEST['name'])) {
			admin_header(_r('Search Results'), 'plugins.php');

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
		break;
}
		admin_footer();
?>