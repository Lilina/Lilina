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
		$headers = Lilina_Updater::update_headers(array(
			'Content-Type' => 'application/json',
		));
		$data = array(
			'component' => $name
		);
		$request = Lilina_HTTP::get('http://api.getlilina.org/plugins/info', $headers, $data);
		if (!$request->success) {
			return false;
		}

		$data = json_decode($request->body);
		$obj = Lilina_Updater_PluginInfo::load($data);
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
		$result = Lilina_HTTP::post('http://api.getlilina.org/plugins/version', $headers, json_encode($plugins));
		if (!$result->success) {
			return false;
		}

		$return = array();

		$data = json_decode($result->body);
		foreach ($data as $name => $pdata) {
			if ($pdata->status === 200) {
				$plugin = new Lilina_Updater_PluginInfo($name);
				$plugin->download = $pdata->body->url;
				$plugin->version = $pdata->body->version;
				$return[$name] = $plugin;
			}
		}

		return $return;
	}

	public function search($query) {
		$headers = Lilina_Updater::update_headers(array(
			'Content-Type' => 'application/json',
		));
		$data = array(
			'system' => array(
				'php' => phpversion(),
				'lilina' => LILINA_CORE_VERSION
			),
			'query' => $query
		);
		$request = Lilina_HTTP::get('http://api.getlilina.org/plugins/search', $headers, $data);
		if (!$request->success) {
			return false;
		}

		return false;
	}
}