<?php

/**
 * Ajax method handler
 * Thanks to Morten Fangel for inspiration
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @subpackage Administration
 */
class AjaxHandler {
	protected $methods;

	public function __construct() {
		do_action('ajax-register', $this);
	}

	/**
	 * Add a new method handler
	 */
	public function registerMethod($method, $callback) {
		$this->methods[$method] = $callback;
	}

	/**
	 * Handle an API call based on method
	 */
	public function handle($method = null, $params = array()) {
		if( !$method || empty($this->methods[$method]) ) {
			// No or invalid method
			throw new Exception('Unknown method: ' . preg_replace('/[^-_.0-9a-zA-Z]/', '', $method), Errors::get_code('admin.ajax.unknown'));
		}

		$callback = $this->methods[ $method ];
		$args = $this->_sortArgs($callback, $params);
		$output = call_user_func_array($callback, $args );
		return apply_filters('ajax_call-' . $method, $output, $callback, $params);
	}

	/**
	 * Sort parameters by order specified in method declaration
	 *
	 * Takes a callback and a list of available params, then filters and sorts
	 * by the parameters the method actually needs, using the reflection APIs
	 *
	 * @author Morten Fangel <fangel@sevengoslings.net>
	 * @param callback $callback
	 * @param array $params
	 * @return array
	 */
	protected function _sortArgs($callback, $params) {
		// Takes a callback and a list or params and filter and
		// sort the list by the parameters the method actually needs
		
		if( is_array($callback) ) {
			$ref_func = new ReflectionMethod($callback[0], $callback[1]);
		} else {
			$ref_func = new ReflectionFunction($callback);
		}
		// Create a reflection on the method
		
		$ref_parameters = $ref_func->getParameters();
		// finds the parameters needed for the function via Reflections
		
		$ordered_parameters = array();
		foreach($ref_parameters AS $ref_parameter) {
			// Run through all the parameters we need
			
			if( isset($params[$ref_parameter->getName()]) ) {
				// We have this parameters in the list to choose from
				$ordered_parameters[] = $params[$ref_parameter->getName()];
			} elseif( $ref_parameter->isDefaultValueAvailable() ) {
				// We don't have this parameter, but it's optional
				$ordered_parameters[] = $ref_parameter->getDefaultValue();
			} else {
				// We don't have this parameter and it wasn't optional, abort!
				throw new Exception('Missing parameter ' . $ref_parameter->getName() . '', Errors::get_code('admin.ajax.missing_param'));
				$ordered_parameters[] = null;
			}
		}
		return $ordered_parameters;
	}
}