<?php
/**
 * Authentication and general user handling
 * @package Lilina
 * @subpackage Classes
 */
class User {
	var $user;
	var $info;
	var $password;

	/**
	 * User() - Constructor for the class
	 */
	function User($user = false, $password = false) {
		if(!$user)
			$user = isset($_POST['user']) ? $_POST['user'] : false;

		if(!$password)
			$password = isset($_POST['password']) ? $_POST['password'] : false;

		$this->user = $user;
		$this->password = $this->hash($password);
	}

	/**
	 * identify() - Check user authentication
	 *
	 * Checks the session variables and cookies to make sure the user is logged in. If they aren't, it
	 * sets a username and password cookie and sets a session variable. The cookie is set to expire in
	 * 2 weeks/14 days.
	 * @return mixed Boolean true if logged in, otherwise passes the result of {@link lilina_check_user_pass()}} through
	 */
	function identify() {

		/** Cookies: Nom nom nom! */
		if(isset($_COOKIE['lilina_user']) && isset($_COOKIE['lilina_pass'])) {
			if($this->check($_COOKIE['lilina_user'], $_COOKIE['lilina_user']) != 1)
				return false;
			return true;
		}

		/** Sessions: Bleh **/
		session_start();
		if (isset( $_SESSION['is_logged_in'] ) && $_SESSION['is_logged_in'] === true) {
			$this->authed = true;
			/** Upgrade to cookies! */
			$this->set_cookies();
			return true;
		}

		/** /me smells a newb. */
		if( $this->authenticate() ) {
			$this->authed = true;
			$this->set_cookies();
			return true;
		}

		/** Uh oh! */
		return false;
	}

	/**
	 * authenticate() - Check supplied credentials
	 *
	 * Checks the supplied username and MD5'd password against the username and password stored in settings
	 * @param string $u Overriding username
	 * @param string $p Overriding password
	 * @return int 1 for correct username and password, -1 if username or password is wrong or 0 if username or password is blank
	 */
	function authenticate($u = false, $p = false) {
		if($u)
			$this->user = $u;
		if($p)
			$this->password = $p;

		if(empty($this->user) || empty($this->password)) {
			return 0;
		}

		if ($this->user === get_option('auth', 'user') && ($password_hash = $this->password) === get_option('auth', 'pass')) {
			return 1;
		}
		else {
			return -1;
		}
	}

	/**
	 * set_cookies() - Sets the authentication cookies for next use
	 *
	 * Does what it says on the tin.
	 * @internal Cookies are nom nom nom. (compared to those ugly sessions)
	 */
	function set_cookies() {
		setcookie ( 'lilina_user', $this->user, time() + 1209600 );
		setcookie ( 'lilina_pass',  $this->password, time() + 1209600 );
	}
}