<?php
/**
 * lilina-favicon.php - Displays SimplePie favicons
 *
 * Generates a Atom feed from the available items.
 * Thanks to Feed on Feeds' favicon.php for inspiration
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @author Feed on Feeds Team
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/** */
define('LILINA_PATH', dirname(__FILE__));
define('LILINA_INCPATH', LILINA_PATH . '/inc');

define('LILINA_PAGE', 'favicon');

// Hide errors
ini_set('display_errors', false);

require_once(LILINA_INCPATH . '/contrib/simplepie.class.php');

require_once(LILINA_INCPATH . '/core/conf.php');
require_once(LILINA_INCPATH . '/core/plugin-functions.php');

if(isset($_GET['feed'])) {
	$feed = Feeds::get_instance()->get($_GET['feed']);
	if ($feed !== false && $feed['icon'] === true) {
		$data = new DataHandler(get_option('cachedir'));
		$data = $data->load($feed['id'] . '.ico');
		if ($data !== null) {
			$icon = unserialize($data);

			header('Content-Type: ' .  $icon['type']);
			header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 604800) . ' GMT'); // 7 days

			echo $icon['body'];
			die();
		}
	}
	$_GET['i'] = 'default';
}

if(!isset($_GET['i']))
	die();

function faux_hash($input) { return $input; }

function display_cached_file($identifier_url, $cache_location = './cache') {
	$cache = SimplePie_Cache::create($cache_location, $identifier_url, 'spi');

	if ($file = $cache->load())
	{
		if (isset($file['headers']['content-type']))
		{
			header('Content-type:' . $file['headers']['content-type']);
		}
		else
		{
			header('Content-type: application/octet-stream');
		}
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 604800) . ' GMT'); // 7 days
		echo $file['body'];
		exit;
	}

	die('Cached file for ' . $identifier_url . ' cannot be found.');
}

if($_GET['i'] != 'default' && file_exists(LILINA_CACHE_DIR . $_GET['i'] . '.spi')) {
	display_cached_file($_GET['i'], LILINA_CONTENT_DIR . '/system/cache');
}
else {
	Locale::load_default_textdomain();

	header('Content-Type: image/png');
	header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 604800) . ' GMT'); // 7 days

	echo file_get_contents(Templates::get_file('feed.png'));
	die();
}

?>