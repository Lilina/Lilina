<?php
/**
 * @todo Move to admin/login.php
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/** */
define('LILINA_LOGIN', true);
require_once('admin.php');

$return = '';
if(!empty($_REQUEST['return']))
	$return = preg_replace('/[^-_.0-9a-zA-Z]/', '', $_REQUEST['return']);

if(isset($_REQUEST['logout'])) {
	$user = new User();
	$user->destroy_cookies();

	if(empty($return))
		$return = 'admin/login.php';

	header('HTTP/1.1 302 Found');
	header('Location: ' . get_option('baseurl') . $return);
	die();
}

if(defined('LILINA_AUTHED') && LILINA_AUTHED === true) {
	if(empty($return))
		$return = 'admin/';

	header('HTTP/1.1 302 Found', true, 302);
	header('Location: ' . get_option('baseurl') . $return);
	header('Connection: close');
	die();
}

global $page;
$body = '';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php _e('Login') ?> &mdash; <?php echo get_option('sitename'); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="<?php echo get_option('baseurl'); ?>admin/resources/core.css" media="screen" />
<link rel="stylesheet" type="text/css" href="<?php echo get_option('baseurl'); ?>admin/resources/mini.css" media="screen" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>admin/admin.js"></script>
</head>
<body id="admin-subscribe" class="admin-page">
	<div id="main">
		<h1><?php _e('Login') ?></h1>
		<p id="backlink"><a href="<?php echo get_option('baseurl'); ?>"><?php echo sprintf(_r('Back to %s'), get_option('sitename')); ?></a></p>
<?php
if(defined('LILINA_AUTH_ERROR') && LILINA_AUTH_ERROR === -1) {
?>
	<div id="error">
		<p><?php _e('Your password or username is incorrect. Please make sure you have spelt it correctly.') ?></p>
	</div>
<?php
}
?>
		<form action="login.php" method="post">
			<fieldset id="login">
				<p>
					<label for="user"><?php _e('Username') ?>:</label>
					<input type="text" name="user" id="user" class="input" />
				</p>

				<p>
					<label for="pass"><?php _e('Password') ?>:</label>
					<input type="password" name="pass" id="pass" class="input" />
				</p>

			</fieldset>

			<input type="hidden" name="page" value="<?php if (isset($page)) echo $page; ?>" />
			<input type="hidden" name="return" value="<?php echo $return ?>" />
			<input type="submit" value="<?php _e('Log in') ?>" class="submit" />
		</form>
	</div>

	<script type="text/javascript" src="<?php echo get_option('baseurl') ?>inc/js/jquery.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			setTimeout("hideAlert()", 700);
		});
		function hideAlert() {
			$(".alert").fadeOut(2000);
		}
	</script>
</body>
</html>