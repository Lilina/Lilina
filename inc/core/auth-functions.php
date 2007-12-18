<?php
/**
* Authentication functions for the administration panel
*
* @author Ryan McCue <cubegames@gmail.com>
* @package Lilina
* @version 1.0
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

defined('LILINA') or die('Restricted access');

/**
* Check actual authentication supplied
*
* Checks the supplied username and MD5'd password to the username and password stored in
* settings.php
* @param string $un Supplied username
* @param string $pw Supplied password
* @return bool True if logged in, false if error
*/
function lilina_auth($un, $pw) {
	global $settings;
	if(!empty($un) && !empty($pw)) {
		//Check the username and password
		if ($un === $settings['auth']['user'] && md5($pw) === $settings['auth']['pass']) {
			//All details good to go, lets
			//indicate we are logged in
			session_regenerate_id();
			$_SESSION['is_logged_in'] = true;
			return true;
		}
		else {
			return 'error';
		}
	}
	return false;
}

/**
* Generates a form for use with the authentication system
*
* @param mixed $error Either "pw" for a password error, "un" for a username error or false
* @return bool Never returns
* @todo Move admin-login.php into here
*/
function lilina_form($error = false) {
	require_once(LILINA_INCPATH . '/pages/admin-login.php');
	die();
}

/**
* Function to authenticate user
*
* @param string $user Supplied username
* @param string $pass Supplied password
* @return bool True if logged in, false if not, however should never return false, since it should die()
*/
function lilina_admin_auth($user, $pass) {
	//Are we logged in?
	$logged_in	= lilina_auth($user, $pass);
	if($logged_in === true) {
		//...Boolean true, we are logged in
		return true;
	}
	elseif($logged_in === false || $logged_in === 'error') {
		//...Boolean false, we aren't logged in, let's show the form
		lilina_form($logged_in);
		return false;
	}
	else {
		//...Unknown, just to be sure, we aren't logged in
		return false;
	}
}
?>