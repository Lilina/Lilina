<?php
/**
 * Service API for the unofficial and undocumented Google Reader API
 */

/**
 * Service API for the unofficial and undocumented Google Reader API
 *
 * Thanks to Dave Shea of mezzoblue.com and the Google Reader documentation
 * by the pyrfeed project.
 *
 * @author Dave Shea <http://mezzoblue.com/>
 * @author Ryan McCue <http://ryanmccue.info/>
 * @link <http://code.google.com/p/pyrfeed/wiki/GoogleReaderAPI>
 *
 * @package Service APIs
 */
class GoogleReaderAPI {
	protected $id;
	protected $pass;
	protected $request;

	public function __construct($user_id = '', $password = '') {
		$this->urls = array(
			'auth' => 'https://www.google.com/accounts/ClientLogin',
			'atom' => 'http://www.google.com/reader/atom',
			'api'  => 'http://www.google.com/reader/api/0/',
		);

		$this->id = $user_id;
		$this->pass = $password;
	}

	public function connect() {
		$this->request = new HTTPRequest('', 10, 'Lilina/' . LILINA_CORE_VERSION);
		$this->authenticate();
	}

	protected function authenticate() {
		$data = array(
			'service' => 'reader',
			'continue' => 'http://www.google.com/',
			'Email' => $this->id,
			'Passwd' => $this->pass,
			'source' => 'Lilina/' . LILINA_CORE_VERSION,
		);

		$response = $this->request->post($this->urls['auth'], array(), $data);

		preg_match('#SID=(.*)#i', $response->body, $results);
		// so we've found the SID
		// now we can build the cookie that gets us in the door
		$this->cookie = 'SID=' . $results[1];
	}

	public function call() {
		// this builds the action we'd like the API to perform
		// in this case, it's getting our list of unread items
		$action = 'http://www.google.com/reader/subscriptions/export';
		// note that the hyphen above is a shortcut
		// for "the currently logged-in user"
		$response = $this->request->get($action, array('Cookie' => $this->cookie));
		
		if($response->success !== true) {
			if(isset($response->headers['location']) && strpos($response->headers['location'], 'https://www.google.com/accounts/ServiceLogin') !== false) {
				// Error text from Google
				throw new Exception(_r('The username or password you entered is incorrect.'), Errors::get_code('admin.importer.greader.invalid_auth'));
			}
		}
		
		// and finally, let's take a look.
		return $response->body;
	}

	public function disconnect() {
	}
}