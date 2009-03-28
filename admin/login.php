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

if(defined('LILINA_AUTHED') && LILINA_AUTHED === true) {
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: ' . get_option('baseurl') . 'admin/index.php');
	header('Connection: close');
	die();
}

global $page;
$body = '';

if(defined('LILINA_AUTH_ERROR') && LILINA_AUTH_ERROR === -1)
	$body = '<p class="alert">' . _r('Your password or username is incorrect. Please make sure you have spelt it correctly.') . '</p>';

$body .= '
	<form action="login.php" method="post">
		<fieldset id="login">
			<div class="row">
				<label for="user">' . _r('Username') . ':</label>
				<input type="text" name="user" id="user" class="input" />
			</div>
			<div class="row">
				<label for="pass">' . _r('Password') . ':</label>
				<input type="password" name="pass" id="pass" class="input" />
			</div>
		</fieldset>
		<input type="hidden" name="page" value="' . (isset($page) ? $page : '') . '" />
		<input type="submit" value="' . _r('Login') . '" class="submit" />
	</form>
	<script type="text/javascript" src="' .  get_option('baseurl') . 'inc/js/jquery.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			setTimeout("hideAlert()", 700);
		});
		function hideAlert() {
			$(".alert").fadeOut(2000);
		}
	</script>';

lilina_nice_die($body, 'Login', 'login');
?>