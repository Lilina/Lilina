<?php
/**
 * Repository interface
 *
 * @package Lilina
 * @subpackage Updater
 */

/**
 * Repository interface
 *
 * @package Lilina
 * @subpackage Updater
 */
interface Lilina_Updater_Repository {
	public function __construct();

	/**
	 * Get the unique ID for a repository
	 *
	 * This is the part plugin IDs are prefixed by, e.g. 'glo'
	 * @return string
	 */
	public function get_id();

	/**
	 * Retrieve the information for a plugin/template by ID
	 *
	 * @param string $name
	 * @return Lilina_Updater_PluginInfo
	 */
	public function get($name);

	/**
	 * Check if the plugins are up to date
	 *
	 * @param array $plugins Keys are plugin IDs, minus the 'repo:'
	 * @return array Values are Lilina_Updater_PluginInfo instances
	 */
	public function check($plugins);
}