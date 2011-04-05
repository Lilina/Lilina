<?php
/**
 * Plugin updater and installer
 *
 * @package Lilina
 * @subpackage Updater
 */

/**
 * Plugin updater and installer
 *
 * @package Lilina
 * @subpackage Updater
 */
class Lilina_Updater_Plugins {
	/**
	 * 
	 */
	protected $actionable = array();

	/**
	 * Callback for the admin_init hook
	 *
	 * Don't call this manually.
	 */
	public static function admin_init() {
		$data = new DataHandler();
		$current = $data->load('plugins.updates.json');
		if ($current === null) {
			add_action('admin_footer', array('Lilina_Updater_Plugins', 'check_all'));
			return;
		}

		$current = json_decode($current);
		foreach ($current->plugins as $plugin) {
			$plugin = Lilina_Updater_PluginInfo::load($plugin);
			self::$actionable[$plugin->id] = $plugin;
		}

		if (43200 > (time() - $current->last_checked)) {
			return;
		}

		add_action('admin_footer', array('Lilina_Updater_Plugins', 'check_all'));
	}

	/**
	 * Check all current plugins for updates
	 *
	 * Don't call this manually.
	 */
	public static function check_all() {
		$activated = array_values($GLOBALS['current_plugins']);
		$plugins = array();
		foreach ($activated as $plugin) {
			$meta = plugins_meta(get_plugin_dir() . '/' . $plugin);
			if ($meta->id === 'unknown' || strpos($meta->id, ':') === false) {
				continue;
			}

			list($repo, $id) = explode(':', $meta->id, 2);

			if (!isset($plugins[$repo])) {
				$plugins[$repo] = array();
			}
			$plugins[$repo][$id] = $meta->version;
		}

		self::$actionable = array();
		foreach ($plugins as $repo_id => $tocheck) {
			$repo = Lilina_Updater::get_repository($repo_id);
			if ($repo === null) {
				continue;
			}

			$result = $repo->check($tocheck);
			if (!is_array($result) || empty($result)) {
				continue;
			}

			foreach ($result as $plugin) {
				self::$actionable[$repo_id . ':' . $plugin->id] = $plugin;
			}
		}

		self::$actionable = apply_filters('updater.plugin.aftercheck', self::$actionable, $plugins);

		$to_save = array();
		foreach (self::$actionable as $id => $plugin) {
			$to_save[$id] = $plugin->dump();
		}
		$values = array(
			'plugins' => $to_save,
			'last_checked' => time()
		);
		$data = new DataHandler();
		$data->save('plugins.updates.json', json_encode($values));
	}

	/**
	 * Check whether a plugin needs updating
	 *
	 * @param string $id
	 * @return boolean
	 */
	public static function check($id) {
		if (!empty(self::$actionable[$id])) {
			return true;
		}
		return false;
	}

	/**
	 * Retrieve the information for a plugin
	 *
	 * @param string $name ID string, with prefix
	 * @return Lilina_Updater_PluginInfo
	 */
	public static function get_info($name) {
		list($repo, $name) = explode(':', $name, 2);
		$repo = Lilina_Updater::get_repository($repo);
		return $repo->get($name); 
	}

	/**
	 * Install a plugin
	 *
	 * If a non-prefixed name is specified, this will search all repositories
	 * for a plugin matching the name. This is dangerous, and if more than one
	 * package is found, this will fail. Please prefix all plugin names unless
	 * you know what you're doing.
	 *
	 * @param string $name Either a raw plugin name, e.g. 'instapaper', or a prefixed one, e.g. 'glo:instapaper'
	 * @return boolean
	 */
	public static function install($name) {
		// Make sure we don't time out
		@set_time_limit(300);

		// If the name doesn't specify a repository, ask them all
		if (strpos($name, ':') === false) {
			$available = array();
			foreach (Lilina_Updater::get_repositories() as $repository) {
				if ($package = $repository->get($name)) {
					$available[$repository->get_id()] = $package;
				}
			}
			if (empty($available)){
				throw new Lilina_Updater_Exception(_r('No package found'), 'nopackage');
			}

			if (count($available) > 1) {
				throw new Lilina_Updater_Exception(_r('More than one package was found'), 'toomanypackages', $available);
			}

			$info = array_shift($available);
		}
		else {
			list($repo, $name) = explode(':', $name, 2);
			$repo = Lilina_Updater::get_repository($repo);
			$info = $repo->get($name);
		}

		$filename = LILINA_PATH . '/content/system/temp/' . $info->id . '-' . $info->version . '.zip';
		$headers = array();
		$options = array(
			'filename' => $filename
		);
		$response = Lilina_HTTP::get($info->download, $headers, array(), $options);
		if (!$response->success) {
			throw new Lilina_Updater_Exception(_r('Package could not be downloaded'), 'httperror', $response);
		}
		
		Lilina_Updater::unzip($filename, LILINA_PATH . '/content/plugins/');

		unlink($filename);
		return true;
	}
}