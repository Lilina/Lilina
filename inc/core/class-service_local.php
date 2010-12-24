<?php
/**
 * Local service base class
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * Local service base class
 *
 * Base for any services which use local methods (i.e. via the Controller)
 * @package Lilina
 * @subpackage Services
 */
abstract class Service_Local implements Service {
	/**
	 * Name of the service
	 *
	 * Default is 'Unnamed Service', set as translated string in {@link __construct}
	 * @var string
	 */
	protected $name;

	/**
	 * Description of the service
	 *
	 * Default is 'No description for [service]', set as translated string in {@link __construct}
	 * @var string
	 */
	protected $description;

	/**
	 * Label for the service
	 *
	 * Default is 'Send to [service]', set as translated string in {@link __construct}
	 * @var string
	 */
	protected $label;

	/**
	 * Type of the service
	 *
	 * Either 'inline' or 'external'
	 * @var string
	 */
	protected $type = 'inline';

	/**
	 * Method name
	 * @var string
	 */
	protected $method = 'unknown';
	private $action;

	public function __construct() {
		if (empty($this->name)) {
			$this->name = _r('Unnamed Service');
		}
		if (empty($this->description)) {
			$this->description = sprintf(_r('No description for %s'), $this->name);
		}
		if (empty($this->label)) {
			$this->label = sprintf(_r('Send to %s'), $this->name);
		}
		$this->action = get_option('baseurl') . '?method=' . $this->method . '&item={hash}';
	}

	public function action() {
		return $this->action;
	}

	public function set_action($action) {
		$this->action = $action;
	}

	public function export() {
		return array(
			'name' => $this->name,
			'description' => $this->description,
			'label' => $this->label,
			'type' => $this->type,
			'action' => $this->action
		);
	}
}