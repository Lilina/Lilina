<?php
/**
 * Authentication and general user handling
 * @package Lilina
 * @subpackage Classes
 */
class User {
	/**
	 * Contains the supplied username
	 * @var string
	 */
	var $user;

	/**
	 * Contains the (hashed) supplied password
	 * @var string
	 */
	var $password;

	/**
	 * Contains the unhashed supplied password
	 * @var string
	 */
	var $raw;

	/**
	 * Path on $domain the cookie is valid for
	 * @var string
	 */
	var $path;

	/**
	 * Domain the cookie is valid for
	 * @var string|bool
	 */
	var $domain;

	/**
	 * Constructor for the class
	 */
	public function __construct($user = false, $password = false, $domain = false, $path = null) {
		if(!$user)
			$user = isset($_POST['user']) ? $_POST['user'] : false;

		if(!$password)
			$password = isset($_POST['pass']) ? $_POST['pass'] : false;

		if($path === null)
			$path = preg_replace('|https?://[^/]+|i', '', get_option('baseurl') );

		$this->user = $user;
		$this->raw = $password;
		$this->password = $this->hash($password);
		$this->domain = $domain;
		$this->path = $path;
	}

	/**
	 * Check user authentication
	 *
	 * Checks the session variables and cookies to make sure the user is logged in. If they aren't, it
	 * sets a username and password cookie and sets a session variable. The cookie is set to expire in
	 * 2 weeks/14 days.
	 * @return mixed Boolean true if logged in, otherwise passes the result of {@link lilina_check_user_pass()}} through
	 */
	function identify() {
		if(isset($_COOKIE['lilina_user']) && isset($_COOKIE['lilina_pass'])) {
			if( ( $status = $this->authenticate($_COOKIE['lilina_user'], $_COOKIE['lilina_pass']) ) !== 1)
				return $status;
			return true;
		}

		if( ($status = $this->authenticate()) === 1 ) {
			$this->authed = true;
			$this->set_cookies();
			return true;
		}

		/** Uh oh! */
		return $status;
	}

	/**
	 * Generate a cryptographic hash of supplied string
	 *
	 * Generates the correct hash
	 * @param string $password
	 */
	function hash($password) {
		// Check for MD5
		if(strlen(get_option('auth', 'pass')) === 32)
			return hash('md5', $password);
		
		return hash('sha512', get_option('salt') . $password);
	}

	/**
	 * Upgrade the password from MD5 to SHA512
	 *
	 * Checks the password length to determine type
	 * @param string $u Overriding username
	 * @param string $p Overriding password
	 * @return bool True if password has been "upgraded", false otherwise
	 */
	function upgrade() {
		if(strlen($this->password) !== 32)
			return true;

		if($this->password !== get_option('auth', 'pass'))
			return false;

		$this->new_password = hash('sha512', get_option('salt') . $this->raw);
		return true;
	}

	/**
	 * Check supplied credentials
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

		if ($this->user === get_option('auth', 'user') && $this->password === get_option('auth', 'pass')) {
			return 1;
		}
		else {
			return -1;
		}
	}

	/**
	 * Set the authentication cookies for next use
	 *
	 * Does what it says on the tin. Uses HttpOnly for both cookies.
	 * @internal Cookies are nom nom nom. (compared to those ugly sessions)
	 */
	function set_cookies() {
		setcookie ( 'lilina_user', $this->user, time() + 1209600, $this->path, $this->domain, null, true );
		setcookie ( 'lilina_pass', $this->password, time() + 1209600, $this->path, $this->domain, null, true );
	}

	/**
	 * Remove authentication cookies
	 *
	 * Removes cookies by setting value to a blank string and setting the expiry time in the past.
	 * @internal Cookies are nom nom nom. (compared to those ugly sessions)
	 */
	function destroy_cookies() {
		setcookie ( 'lilina_user', '', time() - 31536000, $this->path, $this->domain );
		setcookie ( 'lilina_pass', '', time() - 31536000, $this->path, $this->domain );
	}
}