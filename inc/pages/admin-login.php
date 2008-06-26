<?php
/**
 * @todo Move to admin/login.php
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

defined('LILINA_PATH') or die('Restricted access');

$body = '';

if(isset($error) && $error == 'error')
	$body = '<p class="alert">' . _r('Your password or username is incorrect. Please make sure you have spelt it correctly.') . '</p>';

$body .= '
	<form action="' . $_SERVER['PHP_SELF'] . '" method="post">
		<div class="row">
			<label for="user">' . _r('Username') . ':</label>
			<input type="text" name="user" id="user" />
		</div>
		<div class="row">
			<label for="pass">' . _r('Password') . ':</label>
			<input type="password" name="pass" id="pass" />
		</div>
		<input type="hidden" name="page" value="' . (isset($page) ? $page : '') . '" />
		<input type="submit" value="' . _r('Login') . '" />
	</form>';

lilina_nice_die($body, 'Login');
?>