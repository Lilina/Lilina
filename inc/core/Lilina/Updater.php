<?php

class Lilina_Updater {
	private static $repositories;
	public static function register(Lilina_Updater_Repository $repo) {
		Lilina_Updater::$repositories[$repo->get_id()] = $repo;
	}

	public static function get_repository($name) {
		if (!empty(Lilina_Updater::$repositories[$name])) {
			return Lilina_Updater::$repositories[$name];
		}
		return null;
	}
	public static function get_repositories() {
		return Lilina_Updater::$repositories;
	}

	public static function update_headers($custom = array()) {
		$id = sha1(get_option('baseurl'));
		$headers = apply_filters('update_http_headers', array('X-Install-ID' => $id));
		return array_merge($headers, $custom);
	}

	/**
	 * Unzip an archive
	 *
	 * @param string $package Zip filename
	 * @param string $destination Destination directory
	 * @throws Lilina_Updater_Exception
	 * @return boolean
	 */
	public static function unzip($package, $destination) {
		if (class_exists('ZipArchive') and false) {
			return Lilina_Updater::unzip_ziparchive($package, $destination);
		}
		else {
			return Lilina_Updater::unzip_pclzip($package, $destination);
		}
	}

	/**
	 * Use the Zip extension to unzip an archive
	 *
	 * @param string $package Zip filename
	 * @param string $destination Destination directory
	 * @throws Lilina_Updater_Exception
	 * @return boolean
	 */
	protected static function unzip_ziparchive($package, $destination) {
		$needed = array();
		$zip = new ZipArchive();

		$zopen = $zip->open($package, ZIPARCHIVE::CHECKCONS);
		if ($zopen !== true) {
			throw new Lilina_Updater_Exception('Incompatible archive', 'ziparchive_incompat');
		}

		for ( $num = 0; $num < $zip->numFiles; $num++ ) {
			if ( ! $info = $zip->statIndex($num) ) {
				throw new Lilina_Updater_Exception('Could not retrieve file from archive', 'ziparchive_stat_failed');
			}

			if ('/' == substr($info['name'], -1)) { // directory
				if (!file_exists($destination . $info['name'])) {
					mkdir($destination . $info['name'], 0755);
				}
				continue;
			}

			if ('__MACOSX/' === substr($info['name'], 0, 9)) {
				// Don't extract the OS X-created __MACOSX directory files
				continue;
			}

			$contents = $zip->getFromIndex($num);
			if ($contents === false) {
				throw new Lilina_Updater_Exception(_r('Could not extract file from archive'), 'ziparchive_extract_failed', $info['name']);
			}

			if (!file_put_contents($destination . $info['name'], $contents)) {
				throw new Lilina_Updater_Exception(_r('Could not copy file'), 'ziparchive_copy_fail', $destination . $info['name']);
			}
		}

		$zip->close();
		return true;
	}

	/**
	 * Use PclZip to unzip an archive
	 *
	 * @param string $package Zip filename
	 * @param string $destination Destination directory
	 * @throws Lilina_Updater_Exception
	 * @return boolean
	 */
	protected static function unzip_pclzip($package, $destination) {
		require_once(LILINA_INCPATH . '/contrib/pclzip/pclzip.lib.php');
		$archive = new PclZip($package);

		if (($archive_files = $archive->extract(PCLZIP_OPT_EXTRACT_AS_STRING)) === false) {
			throw new Lilina_Updater_Exception('Incompatible archive', 'pclzip_incompat', $archive->errorInfo(true));
		}

		if (count($archive_files) === 0) {
			throw new Lilina_Updater_Exception('Empty archive', 'pclzip_empty');
		}

		foreach ($archive_files as $file) {
			if ($file['folder']) {
				if (!file_exists($destination . $file['filename'])) {
					mkdir($destination . $file['filename'], 0755);
				}
				continue;
			}

			if ('__MACOSX/' === substr($file['filename'], 0, 9)) {
				// Don't extract the OS X-created __MACOSX directory files
				continue;
			}

			if (!file_put_contents($destination . $file['filename'], $file['content'])) {
				throw new Lilina_Updater_Exception(_r('Could not copy file'), 'pclzip_copy_fail', $destination . $file['filename']);
			}
		}
		return true;
	}

	/**
	 * Copy a directory recursively
	 *
	 * @param string $from Directory to copy from
	 * @param string $to Directory to copy to
	 * @throws Lilina_Updater_Exception
	 */
	public static function copydir($from, $to) {
		if (substr($to, -1) !== '/') {
			$to .= '/';
		}
		$from = new RecursiveDirectoryIterator($from, FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_SELF | FilesystemIterator::SKIP_DOTS);
		$from = new RecursiveIteratorIterator($from, RecursiveIteratorIterator::SELF_FIRST);
		foreach ($from as $path => $file) {
			if ($file->isFile()) {
				if (!copy($path, $to . $file->getSubPathName())) {
					chmod($to . $file->getSubPathName(), 0644);
					if (!copy($path, $to . $file->getSubPathName())) {
						throw new Lilina_Updater_Exception(_r('Could not copy file'), 'copydir_copy_fail', $to . $file->getSubPathName());
					}
				}
			}
			else {
				if (file_exists($to . $file->getSubPathName()) && is_dir($to . $file->getSubPathName())) {
					continue;
				}
				if (!mkdir($to . $file->getSubPathName(), 0755)) {
					throw new Lilina_Updater_Exception(_r('Could not create directory'), 'copydir_kdir_fail', $to . $file->getSubPathName());
				}
			}
		}
	}

	/**
	 * Remove a directory recursively
	 *
	 * @param string $path
	 * @return boolean
	 */
	public static function rmdir($root) {
		$dir = new RecursiveDirectoryIterator($root, FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_SELF | FilesystemIterator::SKIP_DOTS);
		$dir = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($dir as $path => $file) {
			if ($file->isFile()) {
				unlink($path);
			}
			else {
				rmdir($path);
			}
		}

		return rmdir($root);
	}

	/**
	 * Unzip into a temporary directory and copy the files into the actual directory
	 *
	 * @param string $zip Zip filename
	 * @param string $tempdir
	 * @param string $realdir
	 * @return boolean
	 */
	public static function unzipandcopy($zip, $tempdir, $realdir) {
		// Ensure we clean up if last time failed
		if (file_exists($tempdir) && is_dir($tempdir)) {
			Lilina_Updater::rmdir($tempdir);
		}

		Lilina_Updater::unzip($zip, $tempdir);
		unlink($zip);

		// Now, move the files into the correct directory
		if (!file_exists($realdir) || !is_dir($realdir)) {
			mkdir($realdir, 0755);
		}
		Lilina_Updater::copydir($tempdir, $realdir);
		Lilina_Updater::rmdir($tempdir);
		return true;
	}
}

$glo = new Lilina_Updater_Repository_GLO();
Lilina_Updater::register($glo);