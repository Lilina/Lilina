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
	protected $user;

	/**
	 * Contains the (hashed) supplied password
	 * @var string
	 */
	protected $password;

	/**
	 * Contains the unhashed supplied password
	 * @var string
	 */
	protected $raw;

	/**
	 * Path on $domain the cookie is valid for
	 * @var string
	 */
	protected $path;

	/**
	 * Domain the cookie is valid for
	 * @var string|bool
	 */
	protected $domain;

	protected $site;

	/**
	 * Constructor for the class
	 */
	public function __construct($user = false, $password = false) {
		if(!$user)
			$user = isset($_POST['user']) ? $_POST['user'] : false;

		if(!$password)
			$password = isset($_POST['pass']) ? $_POST['pass'] : false;

		$this->user = $user;
		$this->raw = $password;
		$this->password = $this->hash($password);
		$this->domain = apply_filters('user.cookie.domain', parse_url( get_option('baseurl'), PHP_URL_HOST ));
		$this->path   = apply_filters('user.cookie.path', parse_url( get_option('baseurl'), PHP_URL_PATH ));
		$this->site = sha1(get_option('baseurl'));
	}

	/**
	 * Check user authentication
	 *
	 * Checks the session variables and cookies to make sure the user is logged in. If they aren't, it
	 * sets a username and password cookie and sets a session variable. The cookie is set to expire in
	 * 2 weeks/14 days.
	 * @return mixed Boolean true if logged in, otherwise passes the result of {@link lilina_check_user_pass()}} through
	 */
	public function identify() {
		if (isset($_COOKIE['lilinaauth_' . $this->site])) {
			return $this->check_cookie($_COOKIE['lilinaauth_' . $this->site]);
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
	protected function hash($password) {
		// Check for MD5
		if(strlen(get_option('auth', 'pass')) === 32)
			return hash('md5', $password);
		
		$hash = new PasswordHash(8, false);
		return $hash->CheckPassword($password, get_option('auth', 'pass'));
	}

	/**
	 * Upgrade the password from MD5 to SHA512
	 *
	 * Checks the password length to determine type
	 * @param string $u Overriding username
	 * @param string $p Overriding password
	 * @return bool True if password has been "upgraded", false otherwise
	 */
	protected function upgrade() {
		if(strlen($this->password) !== 32)
			return true;

		if($this->password !== get_option('auth', 'pass'))
			return false;

		$hash = new PasswordHash(8, false);
		$this->new_password = $hash->HashPassword($this->raw);
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
	public function authenticate($u = false, $p = false) {
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

	protected function create_cookie($expiration) {
		$pass = substr($this->password, 8, 4);
		$key = sha1($this->user . $pass . '|' . $expiration);
		$hash = hash_hmac('sha1', $this->user . '|' . $expiration, $key);

		$cookie = $this->user . '|' . $expiration . '|' . $hash;
		return apply_filters('user.cookie.value', $cookie, $this);
	}

	protected function check_cookie($cookie) {
		$bits = explode('|', $cookie);
		if (count($bits) !== 3) {
			return false;
		}

		$this->user = $bits[0];
		$expiration = $bits[1];
		// TODO: Fix this for multi-user
		$this->password = get_option('auth', 'pass');
		if ($expiration < time()) {
			return false;
		}

		$real = $this->create_cookie($expiration);
		return $cookie === $real;
	}

	/**
	 * Set the authentication cookies for next use
	 *
	 * Does what it says on the tin. Uses HttpOnly for both cookies.
	 * @internal Cookies are nom nom nom. (compared to those ugly sessions)
	 */
	protected function set_cookies() {
		$expiration = time() + 1209600;
		setcookie( 'lilinaauth_' . $this->site, $this->create_cookie($expiration), $expiration, $this->path, $this->domain, null, true );
	}

	/**
	 * Remove authentication cookies
	 *
	 * Removes cookies by setting value to a blank string and setting the expiry time in the past.
	 * @internal Cookies are nom nom nom. (compared to those ugly sessions)
	 */
	public function destroy_cookies() {
		setcookie ( 'lilinaauth_' . $this->site, '', time() - 31536000, $this->path, $this->domain );
	}

	public static function create($user, $password) {
		
		return new User($user, $password);
	}
}