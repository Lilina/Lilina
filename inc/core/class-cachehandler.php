<?php
/**
 * Cache class
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class CacheHandler extends DataHandler {
	/**
	 * Number of seconds to keep cache for.
	 *
	 * @since 1.0
	 *
	 * @var int
	 */
	protected static $expiration;

	/**
	 * Constructor, duh.
	 *
	 * @since 1.0
	 * @uses $directory Holds the cache directory, which the constructor sets.
	 *
	 * @param string $directory 
	 */
	public function __construct($directory = null, $expiration = 3600) {
		if($directory === null)
			$directory = get_option('cachedir');

		$this->expiration = (int) $expiration;
		parent::__construct($directory);
	}

	/**
	 * Begin the caching process
	 *
	 * Loads the cache if valid. If not, begins the output buffer
	 *
	 * @since 1.0
	 * @uses load()
	 *
	 * @param $id Unique ID for content type, used to distinguish between different caches
	 */
	public function begin_caching($id) {
		if(apply_filters('cache_result', $content = $this->load($id)) !== null)
			die(apply_filters('cache_pre_display', $content));

		ob_start();
	}

	/**
	 * End the caching process
	 *
	 * Gets the content from the output buffer and saves to the file
	 *
	 * @since 1.0
	 * @uses save()
	 *
	 * @param $id Unique ID for content type, used to distinguish between different caches
	 */
	public function end_caching($id) {
		$contents = apply_filters('cache_pre_save', ob_get_contents());
		$this->save($id, $contents);
	}

	/**
	 * Ends the output handler
	 *
	 * Saves output as a cached file and flushes the output cache to the display
	 *
	 * @since 1.0
	 * @uses $directory
	 * @uses put()
	 *
	 * @param string $id Unique ID for content type, used to distinguish between different caches
	 * @param string $content Content to save to cache
	 */
	public function save($id, $content) {
		$file = $this->directory . md5($id) . '.cache';

		if(!$this->put($file, $content)) {
			trigger_error(get_class($this) . " error: Couldn't write to $file", E_USER_WARNING);
			return false;
		}

		return true;
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
	public function load($id) {
		return $this->get($this->directory . md5($id) . '.cache');
	}

	/**
	 * Checks the cache.
	 *
	 * Checks the cache to find out whether to use the
	 * cached file or not.
	 *
	 * @since 1.0
	 * @uses $directory
	 * @uses expired() Check the filetime to see if file is valid
	 *
	 * @param string $id Unique ID for content type, used to distinguish between different caches
	 * @return bool False if the cache doesn't exist or is invalid, otherwise true
	 */
	protected function check($filename){
		if (!file_exists($filename))
			return false;

		/** Show file from cache if still valid */
		return !$this->expired($filename);
	}

	/**
	 * Check the expiration time on supplied file.
	 *
	 * Checks if the supplied file has expired.
	 * 
	 * @since 1.0
	 * @uses $expiration
	 *
	 * @param string $file Filename to check against
	 */
	protected function expired($file) {
		$expires = filemtime($file) + $this->expiration;
		return (time() > $expires);
	}
}

?>