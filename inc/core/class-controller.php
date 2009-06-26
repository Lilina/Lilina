<?php
/**
 * Our controller, for MVC-ish flow
 *
 * @package Lilina
 */

class Controller {
	protected $methods;

	/**
	 * Constructor
	 *
	 * Hook into the controller-register action to add your own methods
	 */
	public function __construct() {
		do_action('controller-register', $this);
		$this->registerMethod('default', array('Templates', 'load'));
	}

	/**
	 * Register a new method response
	 *
	 * Usually called via the controller-register method.
	 * @param string $method Method to register against
	 * @param callback $callback
	 */
	public function registerMethod($method, $callback) {
		$this->methods[$method] = $callback;
	}

	/**
	 * Dispatches the current URL and executes an assigned callback
	 */
	public function dispatch() {
		$method = 'default';
		if(isset($_GET['method']))
			$method = preg_replace('/[^-_.0-9a-zA-Z]/', '', $_GET['method']);

		try {
			if( !$method || empty($this->methods[$method]) ) {
				// No or invalid method
				throw new Exception('Unknown method: ' . $method);
			}

			$callback = $this->methods[$method];
			$output = call_user_func($callback);
		} catch (Exception $e) {
			lilina_nice_die('<p>An error occured dispatching a method: ' . $e->getMessage() . '</p>');
		}
	}
}