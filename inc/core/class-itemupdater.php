<?php
/**
 * This class handles the updating of items from feeds
 *
 * @package Lilina
 */

/**
 * This class handles the updating of items from feeds.
 *
 * Contains both command line and browser interfaces.
 * @package Lilina
 */
class ItemUpdater {
	protected static $cmdline = false;

	/**
	 * Registers the method with Controller
	 */
	public static function register(&$controller) {
		$controller->registerMethod('update', array('ItemUpdater', 'init'));
	}
	/**
	 * Initialises processing of feeds
	 */
	public static function init() {
		self::$cmdline = (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']));
		self::header();
		
		add_action('itemcache-log', array('ItemUpdater', 'log'), 10, 2);
		
		foreach(Feeds::get_instance()->getAll() as $the_feed)
			$feed_list[] = $the_feed['feed'];
		
		$cache = ItemCache::get_instance();
		$cache->set_feeds($feed_list);
		$cache->init(true);
		
		self::footer();
	}

	public static function bootstrap() {
		define('LILINA_PATH', dirname(dirname(dirname(__FILE__))));
		var_dump(LILINA_PATH);
		define('LILINA_INCPATH', LILINA_PATH . '/inc');
		$settings = array();

		require_once(LILINA_INCPATH . '/core/install-functions.php');
		lilina_check_installed();

		require_once(LILINA_INCPATH . '/core/conf.php');
		lilina_level_playing_field();

		require_once(LILINA_INCPATH . '/core/plugin-functions.php');
		$timer_start = lilina_timer_start();

		require_once(LILINA_INCPATH . '/core/version.php');

		Locale::load_default_textdomain();

		require_once(LILINA_INCPATH . '/core/feed-functions.php');
		require_once(LILINA_INCPATH . '/core/file-functions.php');
	}

	/**
	 * Prints header for page, depending on whether we're using the command line
	 * or the browser.
	 */
	protected static function header() {
		if(self::$cmdline === true) {
			echo "----------------------------------------\n";
			_e('Lilina Item Updater');
			echo "\n";
			echo "----------------------------------------\n";
			echo sprintf(_r('Installation Name: %s'), get_option('sitename')) . "\n";
			echo sprintf(_r('URL: %s'), get_option('baseurl')) . "\n";
			echo "----------------------------------------\n";
		}
		else {
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php _e('Item Updater') ?> &mdash; <?php echo get_option('sitename') ?></title>
		<style type="text/css">
			@import "<?php echo get_option('baseurl') ?>install.css";
		</style>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	</head>
	<body<?php if($class !== false) echo ' class="' . $class . '"'; ?>>
		<div id="container">
			<h1><?php _e('Lilina Item Updater') ?></h1>
			<p><?php echo sprintf(_r('Installation Name: %s'), get_option('sitename')) ?></p>
			<p><?php echo sprintf(_r('URL: %s'), get_option('baseurl')) ?></p>
			<hr />
<?php
		}
	}

	public static function footer() {
		if(self::$cmdline === true) {
			echo "----------------------------------------\n";
			_e('Finished updating!');
		}
		else {
?>
			<p><?php _e('Finished updating!') ?></p>
			<img id="logo" src="<?php echo get_option('baseurl') ?>admin/logo-small.png" alt="<?php _e('Lilina Logo') ?>" />
		</div>
	</body>
</html>
<?php
		}
	}

	public static function log($type, $detail) {
		switch($type) {
			case 'update':
				self::println(sprintf(_r('Updating item: %s'), $detail->hash));
				self::println(sprintf(_r('Title: %s'), $detail->title));
				break;
			case 'insert':
				self::println(sprintf(_r('Inserting item: %s'), $detail->hash));
				self::println(sprintf(_r('Title: %s'), $detail->title));
				break;
			case 'notice':
				self::println(sprintf(_r('Notice: %s'), $detail));
				break;
			case 'error':
				self::println(sprintf(_r('Error: %s'), $detail));
				break;
		}
	}

	public static function println($text) {
		if(self::$cmdline === true) {
			echo $text . "\n";
		}
		else {
			echo htmlentities($text) . "<br />\n";
			flush();
		}
	}
}

if(function_exists('add_action'))
	add_action('controller-register', array('ItemUpdater', 'register'), 10, 1);

if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']) && !defined('LILINA_PATH')) {
	ItemUpdater::bootstrap();
	ItemUpdater::init();
}