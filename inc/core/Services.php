<?php
/**
 * Service handler
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * Service handler
 *
 * Takes care of registration and retrieval for services
 * @package Lilina
 * @subpackage Services
 */
class Services {
	protected static $services = array();
	protected static $current;

	/**
	 * Register a service
	 *
	 * @param string $id Unique identifier
	 * @param Service $service Object obeying the Service interface
	 */
	public static function register($id, $options) {
		Services::$services[$id] = $options;
	}

	/**
	 * Retrieve all services (unfiltered)
	 *
	 * @return array Registered services
	 */
	public static function get_all() {
		return Services::$services;
	}

	/**
	 * Retrieve services for an item
	 *
	 * @param stdObject $item
	 * @return array
	 */
	public static function get_for_item($item) {
		Services::$current = $item;
		$services = array_map(array('Services', 'replace'), Services::$services);
		$services = array_map(array('Services', 'export'), $services);
		return $services;
	}

	/**
	 * Replace tokens in the action with values from the current item
	 *
	 * @param Service $value Service to replace tokens for
	 */
	protected static function replace($value) {
		$value = clone $value;
		$value->set_action(
			preg_replace_callback(
				'#\{([^}]+)\}#i',
				array('Services', 'replace_token'),
				$value->action()
			)
		);
		return $value;
	}

	/**
	 * Replace tokens in a string with corresponding keys from the current item
	 *
	 * @param array $matches Matches from preg_replace_callback()
	 * @return string New action
	 */
	protected static function replace_token($matches) {
		if (!empty(Services::$current->$matches[1])) {
			return urlencode(Services::$current->$matches[1]);
		}
		return $matches[0];
	}

	/**
	 * Export handler for array_map()
	 *
	 * @param Service $service
	 * @return array
	 */
	protected static function export($service) {
		return $service->export();
	}
}