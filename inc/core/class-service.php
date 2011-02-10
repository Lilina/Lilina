<?php
/**
 * Service interface
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * Service interface
 *
 * All services registered through Services::register() should conform to this
 * interface.
 * @package Lilina
 * @subpackage Services
 */
interface Service {
	/**
	 * Retrieve the action URL
	 *
	 * @return string Action URL
	 */
	public function action();

	/**
	 * Set the action URL
	 *
	 * Used to set the action URL after replacing tokens in {@link Services::replace}
	 * @param string $action New action
	 */
	public function set_action($action);

	/**
	 * Export the service for the API
	 *
	 * The returned array should have the following keys:
	 *    name - Name of the service to show in the administration panel
	 *    description - Description to show in the administration panel
	 *    label - Label to be used in the front-end
	 *    type - One of 'inline' (shown in lightbox, etc.) or 'external'
	 *    action - URL of the service. Tokens should already be replaced as per {@link Services::replace}
	 *    icon - Icon for use in interface. Size should be 16px x 16px
	 *
	 * @return array Exported version of the service
	 */
	public function export();
}