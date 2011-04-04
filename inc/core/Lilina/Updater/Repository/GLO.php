<?php
/**
 * Repository for GetLilina.org
 *
 * @package Lilina
 * @subpackage Updater
 */

/**
 * Repository for GetLilina.org
 *
 * @package Lilina
 * @subpackage Updater
 */
class Lilina_Updater_Repository_GLO implements Lilina_Updater_Repository {

	public function __construct() {}

	/**
	 * Get the unique ID for a repository
	 *
	 * This is the part plugin IDs are prefixed by.
	 * @return string
	 */
	public function get_id() {
		return 'glo';
	}

	/**
	 * Retrieve the information for a plugin/template by ID
	 *
	 * @param string $name
	 * @return Lilina_Updater_PluginInfo
	 */
	public function get($name) {
		$obj = new Lilina_Updater_PluginInfo();
		$obj->download = 'http://downloads.wordpress.org/plugin/jetpack.1.1.1.zip';
		//$obj->download = 'http://google.com/';
		return $obj;
	}
	
	/**
	 * Check if the plugins are up to date
	 *
	 * @param array $plugins Keys are plugin IDs, minus the 'repo:'
	 * @return array Values are Lilina_Updater_PluginInfo instances
	 */
	public function check($plugins) {
		$headers = Lilina_Updater::update_headers(array(
			'Content-Type' => 'application/json',
		));
		$request = new HTTPRequest();
		$result = $request->post('http://www.postbin.org/oz8ixf', $headers, json_encode($plugins));
		var_dump($plugins);
		$insta = new Lilina_Updater_PluginInfo('instapaper');
		$insta->download = 'http://downloads.wordpress.org/plugin/jetpack.1.1.1.zip';
		$insta->version = '1.1.1';
		$return = array(
			$insta
		);
		return $return;
	}
}