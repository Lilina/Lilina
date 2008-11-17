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
 * @param string $u Username to check if needed
 * @param string $p Password to check if needed
 * @return mixed Boolean true if logged in, otherwise passes the result of {@link lilina_check_user_pass()}} through
 */
function lilina_auth($u,$p) {
	session_start();
	if (isset( $_COOKIE['lilina_user'] ) &&
	  isset( $_COOKIE['lilina_pass'] ) &&
	  $_COOKIE['lilina_user'] === get_option('auth', 'user') &&
	  $_COOKIE['lilina_pass'] === get_option('auth', 'pass')) {
		return true;
	}
	elseif(is_array( $check = lilina_check_user_pass($u, $p) )) {
		setcookie ( 'lilina_user', $check['u'], time() + 1209600 );
		setcookie ( 'lilina_pass',  $check['p'], time() + 1209600 );
		return true;
	}
	return $check;
}

/**
 * lilina_logout() - {@internal Missing Short Description}}
 *
 * {@internal Missing Long Description}}
 */
function lilina_logout() {
	setcookie ( 'lilina_user', ' ', time() - 31536000 );
	setcookie ( 'lilina_pass', ' ', time() - 31536000 );
	if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time()-42000, '/');
	}
	header('Location: '. get_option('baseurl'));
}

/**
 * lilina_check_user_pass() - Verifies the supplied username and password against the set 
 *
 * Checks the supplied username and MD5'd password against the username and password stored in settings.php
 * @param string $un Supplied username
 * @param string $pw Supplied password
 * @return mixed Array of username and password hash if correct, string 'error' if username or password is wrong, boolean false if username or pass is empty
 */
function lilina_check_user_pass($un, $pw) {
	if(!empty($un) && !empty($pw)) {
		//Check the username and password
		if ($un === get_option('auth', 'user') && ($password_hash = md5($pw)) === get_option('auth', 'pass')) {
			return array('u' => $un, 'p' => $password_hash);
		}
		else {
			return 'error';
		}
	}
	return false;
}

/**
 * lilina_login_form() - Check authentication and display a login form if needed
 *
 * @param string $user Supplied username
 * @param string $pass Supplied password
 * @return bool True if logged in, false if not, however should never return false, since it should die()
 */
function lilina_login_form($user, $pass) {
	if(($error = lilina_auth($user, $pass)) === true) {
		define('LILINA_AUTHED', true);
		return;
	}
	if(!defined('LILINA_LOGIN')) {
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . get_option('baseurl') . 'admin/login.php');
		header('Connection: close');
		die();
	}
	define('LILINA_AUTH_ERROR', $error);
}
?>