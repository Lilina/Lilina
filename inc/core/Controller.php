<?php
/**
 * Our controller, for MVC-ish flow
 *
 * @package Lilina
 */

class Controller {
	protected static $methods;

	protected function __construct() {
	}

	/**
	 * Register a new method response
	 *
	 * Usually called via the controller-register method.
	 * @param string $method Method to register against
	 * @param callback $callback
	 */
	public static function registerMethod($method, $callback) {
		Controller::$methods[$method] = $callback;
	}

	/**
	 * Dispatches the current URL and executes an assigned callback
	 */
	public static function dispatch() {
		$method = 'default';
		if(isset($_REQUEST['method']))
			$method = preg_replace('/[^-_.0-9a-zA-Z]/', '', $_REQUEST['method']);

		$method = apply_filters('controller-method', $method);

		define('LILINA_PAGE', $method);

		try {
			if( !$method || empty(Controller::$methods[$method]) ) {
				// Dynamically load method if possible
				if(file_exists(LILINA_INCPATH . '/core/method-' . $method . '.php')) {
					require_once(LILINA_INCPATH . '/core/method-' . $method . '.php');
				}
			}
			
			// Check again, in case we loaded it last time
			if( !$method || empty(Controller::$methods[$method]) ) {
				// No or invalid method
				throw new Exception(sprintf(_r('Unknown method: %s'), $method));
			}

			$callback = Controller::$methods[$method];
			call_user_func($callback);
		} catch (Exception $e) {
			Lilina::nice_die('<p>' . sprintf(_r('An error occured dispatching a method: %s'), $e->getMessage()) . '</p>');
		}
	}
}