<?php
/**
 * Authentication functions for the administration panel
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

defined('LILINA_PATH') or die('Restricted access');

/**
 * lilina_auth() - Check user authentication
 *
 * Checks the session variables and cookies to make sure the user is logged in. If they aren't, it
 * sets a username and password cookie and sets a session variable. The cookie is set to expire in
 * 2 weeks/14 days.
 *
 * @deprecated Use the User class directly instead.
 *
 * @param string $u Username to check if needed
 * @param string $p Password to check if needed
 * @return mixed {@see User::identify()}}
 */
function lilina_auth($un, $pw) {
	$user = new User($un, $pw);
	return $user->identify();
}

/**
 * lilina_logout() - {@internal Missing Short Description}}
 *
 * {@internal Missing Long Description}}
 */
function lilina_logout() {
	$user = new User();
	$user->destroy_cookies();

	header('HTTP/1.1 302 Found');
	header('Location: ' . get_option('baseurl') . 'admin/login.php');
	die();
}

/**
 * lilina_check_user_pass() - Verifies the supplied username and password against the set 
 *
 * Checks the supplied username and MD5'd password against the username and password stored in settings.php
 * @param string $un Supplied username
 * @param string $pw Supplied password
 * @return mixed mixed {@see User::authenticate()}}
 */
function lilina_check_user_pass($un, $pw) {
	$user = new User($un, $pw);
	return $user->authenticate();
}

/**
 * lilina_login_form() - Check authentication and display a login form if needed
 *
 * @param string $user Supplied username
 * @param string $pass Supplied password
 * @return null
 */
function lilina_login_form($user, $pass) {
	$user = new User($user, $pass);
	$result = $user->identify();
	
	if($result === true) {
		define('LILINA_AUTHED', true);
		return;
	}

	if(!defined('LILINA_LOGIN')) {
		header('HTTP/1.1 302 Found');
		header('Location: ' . get_option('baseurl') . 'admin/login.php');
		header('Connection: close');
		die();
	}
	define('LILINA_AUTH_ERROR', $result);
}
?>