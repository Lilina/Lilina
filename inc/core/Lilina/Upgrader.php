<?php

class Lilina_Upgrader {
	/**
	 * Run the upgrader
	 */
	public static function run() {
		//Make sure Lilina's not installed
		if (Lilina::is_installed()) {
			if (!Lilina::settings_current()) {
				if(isset($_GET['action']) && $_GET['action'] == 'upgrade') {
					upgrade();
				}
				else {
					Lilina::nice_die('<p>Your installation of Lilina is out of date. Please <a href="install.php?action=upgrade">upgrade your settings</a> first</p>');
				}
			}
			else {
				Lilina::nice_die('<p>Lilina is already installed. <a href="index.php">Head back to the main page</a></p>');
			}
		}

		try {
			self::upgrade();
		}
		catch (Exception $e) {
			$string = '<p>Your installation has <strong>not</strong> been upgraded successfully. Here\'s the error:</p>';
			$string .= $e->getMessage();

			Lilina::nice_die($string, 'Failed to Upgrade');
		}

		Lilina::nice_die('<p>Your installation has been upgraded successfully. Now, <a href="index.php">get back to reading!</a></p>', 'Upgrade Successful');
	}

	/**
	 * Move all the old 0.7-ish files around into their correct place
	 */
	protected static function move_old_around() {
		/** Rename possible old files */
		if(@file_exists(LILINA_PATH . '/.myfeeds.data'))
			rename(LILINA_PATH . '/.myfeeds.data', LILINA_PATH . '/content/system/config/feeds.data');
		elseif(@file_exists(LILINA_PATH . '/conf/.myfeeds.data'))
			rename(LILINA_PATH . '/conf/.myfeeds.data', LILINA_PATH . '/content/system/config/feeds.data');
		elseif(@file_exists(LILINA_PATH . '/conf/.feeds.data'))
			rename(LILINA_PATH . '/conf/.feeds.data', LILINA_PATH . '/content/system/config/feeds.data');
		elseif(@file_exists(LILINA_PATH . '/conf/feeds.data'))
			rename(LILINA_PATH . '/conf/feeds.data', LILINA_PATH . '/content/system/config/feeds.data');

		if(@file_exists(LILINA_PATH . '/conf/settings.php'))
			rename(LILINA_PATH . '/conf/settings.php', LILINA_PATH . '/content/system/config/settings.php');
	}

	/**
	 * Upgrade from 0.7 or below
	 *
	 * @param string $title Site title
	 * @param string $url Site URL
	 * @param string $user Username
	 * @param string $pass Password
	 */
	protected static function from_0point7($title, $url, $user, $pass) {
		// 0.7 or below
		$raw_php		= "<?php
// What you want to call your Lilina installation
\$settings['sitename'] = '$SITETITLE';

// The URL to your server
\$settings['baseurl'] = '$BASEURL';

// Username and password to log into the administration panel
\$settings['auth'] = array(
	'user' => '$USERNAME',
	'pass' => '" . md5($PASSWORD) . "'
);

// Version of these settings; don't change this
\$settings['settings_version'] = " . LILINA_SETTINGS_VERSION . ";\n?>";

		if (!($settings_file = @fopen(LILINA_PATH . '/content/system/config/settings.php', 'w+')) || !is_resource($settings_file)) {
			throw new Exception('<p>Failed to upgrade settings: Saving <code>content/system/config/settings.php</code> failed</p>');
		}
		fputs($settings_file, $raw_php);
		fclose($settings_file);
	}

	/**
	 * Perform the actual upgrading
	 *
	 * Upgrades Lilina's settings from whatever version to the latest one
	 */
	protected static function upgrade() {
		global $lilina;
		require_once(LILINA_INCPATH . '/core/version.php');
		require_once(LILINA_INCPATH . '/core/misc-functions.php');

		require_once(LILINA_PATH . '/inc/core/conf.php');

		// Just in case
		unset($BASEURL);
		require(LILINA_PATH . '/content/system/config/settings.php');

		// Do we look like 0.7?
		if (isset($BASEURL) && !empty($BASEURL)) {
			self::from_0point7($SITETITLE, $BASEURL, $USERNAME, $PASSWORD);

			self::upgrade_settings(0);
		}
		// Before we started versioning
		elseif (!isset($settings['settings_version'])) {
			// Between 0.7 and r147
			// Fine to just use existing settings
			$raw_php		= file_get_contents(LILINA_PATH . '/content/system/config/settings.php');
			$raw_php		= str_replace('?>', "// Version of these settings; don't change this\n" .
								"\$settings['settings_version'] = 290;\n?>", $raw_php);

			if(!($settings_file = @fopen(LILINA_PATH . '/conf/settings.php', 'w+')) || !is_resource($settings_file)) {
				throw new Exception('<p>Failed to upgrade settings: Saving <code>content/system/config/settings.php</code> failed</p>');
			}
			fputs($settings_file, $raw_php);
			fclose($settings_file);

			// Then, upgrade properly
			self::upgrade_settings(290);
		}
		// Otherwise, upgrade normally
		elseif ($settings['settings_version'] != $lilina['settings-storage']['version']) {
			self::upgrade_settings($settings['settings_version']);
		}
	}

	/**
	 * Set the stored version
	 *
	 * This is called after upgrading the settings
	 *
	 * @param int|null $version Version to set to. Null for the actual version of Lilina
	 */
	protected static function set_version($version = null) {
		if (empty($version)) {
			$version = LILINA_SETTINGS_VERSION;
		}

		$raw_php		= file_get_contents(LILINA_PATH . '/content/system/config/settings.php');
		$raw_php		= str_replace(
			"\$settings['settings_version'] = " . $current . ";",
			"\$settings['settings_version'] = " . LILINA_SETTINGS_VERSION . ";",
			$raw_php);

		if(!($settings_file = @fopen(LILINA_PATH . '/content/system/config/settings.php', 'w+')) || !is_resource($settings_file)) {
			Lilina::nice_die('<p>Failed to upgrade settings: Saving content/system/config/settings.php failed</p>', 'Upgrade failed');
		}
		fputs($settings_file, $raw_php);
		fclose($settings_file);
	}

	/**
	 * Upgrade the settings to the latest version
	 *
	 * @param int $current Current version of the settings
	 */
	protected static function upgrade_settings($current) {
		$versions = array(297, 302, 339, 480, 500, 501, 502);
		foreach ($versions as $version) {
			// Ignore versions below the current one
			if ($version < $current) {
				continue;
			}

			try {
				$method = 'to_' . $version;
				self::$method();
			}
			catch (Exception $e) {
				// An error occurred, so drop back to the version before the
				// failure and save that, then die
				$current = $version - 1;

				self::set_version($current);

				// Rethrow to catch later
				throw $e;
			}
		}

		self::set_version();

		if (!Options::save()) {
			throw new Exception('<p>Failed to upgrade settings: Saving <code>content/system/config/options.data</code> failed</p>');
		}
	}

	/**
	 * Upgrade to version 297
	 */
	protected static function to_297() {
		Options::lazy_update('offset', 0);
		Options::lazy_update('encoding', 'utf-8');
		if (!Options::get('template', false))
			Options::lazy_update('template', 'razor');
		if (!Options::get('locale', false))
			Options::lazy_update('locale', 'en');
	}

	/**
	 * Upgrade to version 302
	 */
	protected static function to_302() {
		Options::lazy_update('timezone', 'UTC');
	}

	/**
	 * Upgrade to version 339
	 *
	 * It appears we missed this at some point
	 */
	protected static function to_339() {
		if (!Options::get('encoding', false))
			Options::lazy_update('encoding', 'utf-8');
	}

	/**
	 * Upgrade to version 480
	 */
	protected static function to_480() {
		global $settings;
		if (!Options::get('sitename', false)) {
			if(!empty($settings['sitename']))
				Options::lazy_update('sitename', $settings['sitename']);
			else
				Options::lazy_update('sitename', 'Lilina');
		}
	}

	/**
	 * Upgrade to version 500
	 */
	protected static function to_500() {
		if (file_exists(LILINA_PATH . '/content/system/config/feeds.json')) {
			if (
				!is_writable(LILINA_PATH . '/content/system/config/feeds.json') ||
				!rename(LILINA_PATH . '/content/system/config/feeds.json', LILINA_PATH . '/content/system/data/feeds.data')
			) {
				throw new Exception('<p>Unable to move <code>content/system/config/feeds.json</code>' .
					'to <code>content/system/data/feeds.data</code>.' .
					'Make sure your permissions are set correctly then <a href="?action=upgrade">try again</a>.</p>');
			}
		}
	}

	/**
	 * Upgrade to version 501
	 */
	protected static function to_501() {
		global $settings;
		if (!Options::get('baseurl', false)) {
			Options::lazy_update('baseurl', $settings['baseurl']);
		}
	}

	/**
	 * Upgrade to version 502
	 */
	protected static function to_502() {
		if (file_exists(LILINA_PATH . '/content/system/config/options.data') || file_exists(LILINA_PATH . '/content/system/data/options.data')) {
			// We need to recreate this
			$options = array();

			if (!is_writable(LILINA_PATH . '/content/system/data/options.data')) {
				throw new Exception('<p>Unable to write to <code>content/system/data/options.data</code>.
					Make sure your permissions are set correctly then <a href="?action=upgrade">try again</a>.</p>');
			}

			if (file_exists(LILINA_PATH . '/content/system/data/options.data')) {
				$data = file_get_contents(LILINA_PATH . '/content/system/data/options.data');
				$options = unserialize($data);
			}

			if ($options === false) {
				$data = file_get_contents(LILINA_PATH . '/content/system/config/options.data');
				$options = unserialize($data);
			}

			$adapter = new Lilina_DB_Adapter_File();
			foreach ($options as $key => $value) {
				Options::lazy_update($key, $value);
			}

			unlink(LILINA_PATH . '/content/system/config/options.data');
		}
	}
}
