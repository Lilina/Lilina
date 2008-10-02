<?php
/**
 * @todo Move to admin/login.php
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

require_once('admin.php');

global $page;
$body = '';

if(isset($error) && $error == 'error')
	$body = '<p class="alert">' . _r('Your password or username is incorrect. Please make sure you have spelt it correctly.') . '</p>';

$body .= '
	<form action="' . $_SERVER['PHP_SELF'] . '" method="post">
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