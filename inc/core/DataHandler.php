<?php
/**
 * Handler for persistent data files
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * Handler for persistent data files
 *
 * @package Lilina
 */
class DataHandler {
	/**
	 * Directory to store data.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	protected $directory;

	/**
	 * Constructor, duh.
	 *
	 * @since 1.0
	 * @uses $directory Holds the data directory, which the constructor sets.
	 *
	 * @param string $directory 
	 */
	public function __construct($directory = null) {
		if ($directory === null)
			$directory = get_data_dir();

		if (substr($directory, -1) != '/')
			$directory .= '/';

		$this->directory = (string) $directory;
	}

	/**
	 * Prepares filename and content for saving
	 *
	 * @since 1.0
	 * @uses $directory
	 * @uses put()
	 *
	 * @param string $filename Filename to save to
	 * @param string $content Content to save to cache
	 */
	public function save($filename, $content) {
		$file = $this->directory . $filename;

		if(!$this->put($file, $content)) {
			trigger_error(get_class($this) . " error: Couldn't write to $file", E_USER_WARNING);
			return false;
		}

		return true;
	}

	/**
	 * Saves data to file
	 *
	 * @since 1.0
	 * @uses $directory
	 *
	 * @param string $file Filename to save to
	 * @param string $data Data to save into $file
	 */
	protected function put($file, $data, $mode = false) {
		if(file_exists($file) && file_get_contents($file) === $data) {
			touch($file);
			return true;
		}
	
		if(!$fp = @fopen($file, 'wb')) {
			return false;
		}

		fwrite($fp, $data);
		fclose($fp);

		$this->chmod($file, $mode);
		return true;
		
	}

	/**
	 * Change the file permissions
	 *
	 * @since 1.0
	 *
	 * @param string $file Absolute path to file
	 * @param integer $mode Octal mode
	 */
	protected function chmod($file, $mode = false){
		if(!$mode)
			$mode = 0644;
		return @chmod($file, $mode);
	}

	/**
	 * Returns the content of the cached file if it is still valid
	 *
	 * @since 1.0
	 * @uses $directory
	 * @uses check() Check if cache file is still valid
	 *
	 * @param string $id Unique ID for content type, used to distinguish between different caches
	 * @return null|string Content of the cached file if valid, otherwise null
	 */
	public function load($filename) {
		return $this->get($this->directory . $filename);
	}

	/**
	 * Returns the content of the file
	 *
	 * @since 1.0
	 * @uses $directory
	 * @uses check() Check if file is valid
	 *
	 * @param string $id Filename to load data from
	 * @return bool|string Content of the file if valid, otherwise null
	 */
	protected function get($filename) {
		if(!$this->check($filename))
			return null;

		return file_get_contents($filename);
	}

	/**
	 * Check a file for validity
	 *
	 * Basically just a fancy alias for file_exists(), made primarily to be
	 * overriden.
	 *
	 * @since 1.0
	 * @uses $directory
	 *
	 * @param string $id Unique ID for content type, used to distinguish between different caches
	 * @return bool False if the cache doesn't exist or is invalid, otherwise true
	 */
	protected function check($filename){
		return file_exists($filename);
	}

	/**
	 * Delete a file
	 *
	 * @param string $filename Unique ID
	 */
	public function delete($filename) {
		return unlink($this->directory . $filename);
	}
}

?>