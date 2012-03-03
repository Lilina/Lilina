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
	 * Interval between checking for updates
	 *
	 * = 12 hours (in seconds)
	 */
	const CHECKINTERVAL = 43200;

	/**
	 * Plugins which require updating
	 *
	 * @param array
	 */
	protected static $actionable = array();

	/**
	 * Callback for the admin_init hook
	 *
	 * Don't call this manually.
	 */
	public static function admin_init() {
		$current = get_option('plugin_update_status');
		if (empty($current) || empty($current->last_checked)) {
			add_action('admin_footer', array('Lilina_Updater_Plugins', 'check_all'));
			return;
		}

		self::$actionable = $current->plugins;

		if (self::CHECKINTERVAL > (time() - $current->last_checked)) {
			return;
		}

		add_action('admin_footer', array('Lilina_Updater_Plugins', 'check_all'));
	}

	/**
	 * Check all current plugins for updates
	 *
	 * Don't call this manually, it is registered by admin_init()
	 */
	public static function check_all() {
		$activated = Lilina_Plugins::get_activated();
		$plugins = array();

		// Firstly, collect all the plugin data
		foreach ($activated as $plugin) {
			if ($plugin->id === 'unknown' || strpos($plugin->id, ':') === false) {
				continue;
			}

			list($repo, $id) = explode(':', $plugin->id, 2);

			if (!isset($plugins[$repo])) {
				$plugins[$repo] = array();
			}
			$plugins[$repo][$id] = array(
				'meta' => $plugin,
				'version' => $plugin->version
			);
		}

		// Next, query each repository and work out which plugins need updating
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

			foreach ($result as $id => $version) {
				self::$actionable[$repo_id . ':' . $id] = $version;
			}
		}

		self::$actionable = apply_filters('updater.plugin.aftercheck', self::$actionable, $plugins);

		// Finally, save the data for next time
		$values = array(
			'plugins' => self::$actionable,
			'last_checked' => time()
		);
		update_option('plugin_update_status', $values);
	}

	/**
	 * Check whether a plugin needs updating
	 *
	 * @param string $id
	 * @return boolean|stdClass
	 */
	public static function check($id) {
		if (!empty(self::$actionable[$id])) {
			return self::$actionable[$id];
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
	 * Search repositories for by name
	 *
	 * @param string $name
	 * @return array
	 */
	public static function search($name) {
		$available = array();
		if (strpos($name, ':') === false) {
			foreach (Lilina_Updater::get_repositories() as $repository) {
				if ($packages = $repository->search($name)) {
					$available = array_merge($available, $packages);
				}
			}
		}
		else {
			list($repo, $name) = explode(':', $name, 2);
			$repo = Lilina_Updater::get_repository($repo);
			$available = $repo->search($name);
		}

		return $available;
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

		$tempdir = LILINA_PATH . '/content/system/temp/' . $info->id . '-' . $info->version . '/';
		$realdir = LILINA_PATH . '/content/plugins/' . $repo->get_id() . '-' . $info->id;
		Lilina_Updater::unzipandcopy($filename, $tempdir, $realdir);
		return true;
	}

	/**
	 * Update a plugin
	 *
	 * If a non-prefixed name is specified, this will search all repositories
	 * for a plugin matching the name. This is dangerous, and if more than one
	 * package is found, this will fail. Please prefix all plugin names unless
	 * you know what you're doing.
	 *
	 * @param string $name Either a raw plugin name, e.g. 'instapaper', or a prefixed one, e.g. 'glo:instapaper'
	 * @return boolean
	 */
	public static function update($name) {
		// Make sure we don't time out
		@set_time_limit(300);

		if (self::check($name) === false) {
			throw new Lilina_Updater_Exception(_r('Plugin already up-to-date'), 'uptodate');
		}

		$info = self::$actionable[$name];

		$filename = LILINA_PATH . '/content/system/temp/' . $info->id . '-' . $info->version . '.zip';
		$headers = array();
		$options = array(
			'filename' => $filename
		);
		$response = Lilina_HTTP::get($info->download, $headers, array(), $options);
		if (!$response->success) {
			throw new Lilina_Updater_Exception(_r('Package could not be downloaded'), 'httperror', $response);
		}

		$tempdir = LILINA_PATH . '/content/system/temp/' . $info->id . '-' . $info->version . '/';

		$meta = Lilina_Plugins::get($name);
		if ($meta !== null && dirname($meta->filename) != LILINA_PATH . '/content/plugins') {
			$realdir = dirname($meta->filename);
		}
		else {
			$dir = str_replace(':', '-', $name);
			$realdir = LILINA_PATH . '/content/plugins/' . $dir;
		}
		Lilina_Updater::unzipandcopy($filename, $tempdir, $realdir);
		return true;
	}
}