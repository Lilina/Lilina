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
		do_action_ref_array('controller-register', array(&$this));
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
		if(isset($_REQUEST['method']))
			$method = preg_replace('/[^-_.0-9a-zA-Z]/', '', $_REQUEST['method']);

		$method = apply_filters('controller-method', $method);

		define('LILINA_PAGE', $method);

		try {
			if( !$method || empty($this->methods[$method]) ) {
				// Dynamically load method if possible
				if(file_exists(LILINA_INCPATH . '/core/method-' . $method . '.php')) {
					require_once(LILINA_INCPATH . '/core/method-' . $method . '.php');
				}
			}
			
			do_action_ref_array('controller-lateregister', array(&$this));
			
			// Check again, in case we loaded it last time
			if( !$method || empty($this->methods[$method]) ) {
				// No or invalid method
				throw new Exception(sprintf(_r('Unknown method: %s'), $method));
			}

			$callback = $this->methods[$method];
			call_user_func($callback);
		} catch (Exception $e) {
			lilina_nice_die('<p>' . sprintf(_r('An error occured dispatching a method: %s'), $e->getMessage()) . '</p>');
		}
	}
}