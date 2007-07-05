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
* Checks the supplied username and password to the username and password stored in
* settings.php; Takes a MD5 hash of the password as of rev 66
* @param string $un Supplied username
* @param string $pw Supplied password
* @return mixed True if logged in, "pw" if password error, "un" if username error, false otherwise
*/
function lilina_auth($un, $pw) {
	global $settings;
	if(!empty($un) && !empty($pw)) {
		//Check the username and password
		if ($un === $settings['auth']['user']) {
			if($pw === md5($settings['auth']['pass'])) {
				//All details good to go, lets
				//indicate we are logged in
				$_SESSION['is_logged_in'] = true;
				return true;
			}
			else {
				//Error, 
				return 'pw';
			}
		}
		else {
			return 'un';
		}
	}
	else {
		return false;
	}
}

/**
* Generates a form for use with the authentication system
*
* @param mixed $error Either "pw" for a password error, "un" for a username error or false
* @return bool Never returns
* @todo Move admin-login.php into here
*/
function lilina_form($error = false) {
	$highlight_pw	= '';
	$highlight_un	= '';
	if($error) {
		switch($error) {
			case 'pw':
				$error_message = _r('Your password') . ' ' . _r('is incorrect. Please make sure you have spelt it correctly.') . '<br />';
				$highlight_pw	= 'color:#FF615A;';
			break;
			case 'un':
				$error_message = _r('Your username') . ' ' . _r('is incorrect. Please make sure you have spelt it correctly.') . '<br />';
				$higlight_un	= 'color:#FF615A;';
			break;
		}
	}
	require_once('./inc/pages/admin-login.php');
	die();
}

/**
* Function to authenticate user
*
* @param string $user Supplied username
* @param string $pass Supplied password
* @return boolean True if logged in, false if not, however should never return false, since it should die()
*/
function lilina_admin_auth($user, $pass) {
	//Are we logged in?
	$logged_in	= lilina_auth($user, $pass);
	//And we got back...
	if(is_string($logged_in)) {
		//...A string, an error must have been returned
		lilina_form($logged_in);
		return false;
	}
	elseif($logged_in === true) {
		//...Boolean true, we are logged in
		return true;
	}
	elseif($logged_in === false) {
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