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
		require_once(LILINA_INCPATH . '/contrib/simplepie/simplepie.inc');
		ini_set("memory_limit","64M");
		// Each socket has a timeout of 10 seconds, giving it 1 second to
		// process the data, plus 30 seconds for saving and overflow.
		$time_limit = (11 * count(Feeds::get_instance()->getAll())) + 30;
		set_time_limit($time_limit);

		self::$cmdline = (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']));
		self::header();
		
		add_action('itemcache-update', array('ItemUpdater', 'log_update'), 10, 1);
		add_action('itemcache-insert', array('ItemUpdater', 'log_insert'), 10, 1);
		
		self::process();
		
		self::footer();
	}

	/**
	 * Process through the feeds and add the new items to the database
	 */
	public static function process() {
		$updated = false;
		
		foreach(Feeds::get_instance()->getAll() as $feed) {
			self::log('notice', "\n" . sprintf(_r('Retrieving "%s"'), $feed['name']));
			$sp = self::load_feed($feed);
			if($error = $sp->error()) {
				self::log('error', sprintf(_r('An error occurred: %s'), $error));
			}
			
			$items = $sp->get_items();
			foreach($items as $item) {
				$new_item = self::normalise($item, $feed['id']);
				$new_item = apply_filters('item_data_precache', $new_item);
				if(ItemCache::get_instance()->check_item($new_item)) {
					$updated = true;
				}
			}
		}

		ItemCache::get_instance()->sort_all();
		
		if($updated)
			ItemCache::get_instance()->save_cache();
	}

	public static function load_feed($feed) {
		global $lilina;

		$sp = new SimplePie();
		$sp->set_useragent(LILINA_USERAGENT . ' SimplePie/' . SIMPLEPIE_BUILD);
		$sp->set_stupidly_fast(true);
		$sp->set_cache_location(get_option('cachedir'));
		//$sp->set_cache_duration(0);
		$sp->set_favicon_handler(get_option('baseurl') . 'lilina-favicon.php');
		$sp = apply_filters('simplepie-config', $sp);

		$sp->set_feed_url($feed['feed']);
		$sp->init();

		/** We need this so we have something to work with. */
		$sp->get_items();

		if(!isset($sp->data['ordered_items'])) {
			$sp->data['ordered_items'] = $sp->data['items'];
		}

		/** Let's force sorting */
		usort($sp->data['ordered_items'], array(&$sp, 'sort_items'));
		usort($sp->data['items'], array(&$sp, 'sort_items'));

		return $sp;
	}

	/**
	 * Normalise a SimplePie_Item into a stdClass
	 *
	 * Converts a SimplePie_Item into a new-style stdClass
	 */
	public function normalise($item, $feed = '') {
		if($enclosure = $item->get_enclosure()) {
			$enclosure = $enclosure->get_link();
		}
		else {
			// SimplePie_Item::get_enclosure() returns null, so we need to change this to false
			$enclosure = false;
		}
		if($author = $item->get_author()) {
			$author = array(
				'name' => $item->get_author()->get_name(),
				'url' => $item->get_author()->get_link()
			);
		}
		else {
			$author = array(
				'name' => false,
				'url' => false
			);
		}
		$new_item = (object) array(
			'hash'      => $item->get_id(true),
			'timestamp' => $item->get_date('U'),
			'title'     => $item->get_title(),
			'content'   => $item->get_content(),
			'summary'   => $item->get_description(),
			'permalink' => $item->get_permalink(),
			'metadata'  => (object) array(
				'enclosure' => $enclosure
			),
			'author'    => (object) $author,
			'feed'      => $item->get_feed()->get_link()
		);
		if(!empty($feed))
			$new_item->feed_id = $feed;
		return apply_filters('item_data', $new_item);
	}

	public static function bootstrap() {
		define('LILINA_PATH', dirname(dirname(dirname(__FILE__))));
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
			self::println('--------------------------------------------------------------------------------');
			self::println(str_pad(_r('Lilina Item Updater'), 80, ' ', STR_PAD_BOTH));
			self::println('--------------------------------------------------------------------------------');
			self::println(sprintf(_r('Installation Name: %s'), get_option('sitename')));
			self::println(sprintf(_r('URL: %s'), get_option('baseurl')));
			self::println(sprintf(_r('Date: %s'), date('Y-m-d H:m:s')));
			self::println('--------------------------------------------------------------------------------');
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
	<body>
		<div id="container">
			<h1><?php _e('Lilina Item Updater') ?></h1>
			<p><?php echo sprintf(_r('Installation Name: %s'), get_option('sitename')) ?></p>
			<p><?php echo sprintf(_r('URL: %s'), get_option('baseurl')) ?></p>
			<p>	If possible, it is recommended that you access this page via the
				command line, as you can cancel the process if it begins to take
				too long.</p>
			<hr />
			<p>
<?php
		}
	}

	public static function footer() {
		if(self::$cmdline === true) {
			self::println('--------------------------------------------------------------------------------');
			_e('Finished updating!');
			die();
		}
		else {
?>
			</p>
			<p><?php _e('Finished updating!') ?></p>
			<img id="logo" src="<?php echo get_option('baseurl') ?>admin/logo-small.png" alt="<?php _e('Lilina Logo') ?>" />
		</div>
	</body>
</html>
<?php
		}
	}

	public static function log_insert($item) { self::log('insert', $item); }
	public static function log_update($item) { self::log('update', $item); }

	public static function log($type, $detail) {
		switch($type) {
			case 'update':
				self::println(sprintf(_r('Updating item: %s'), $detail->hash));
				self::println("\t" . sprintf(_r('Title: %s'), $detail->title));
				break;
			case 'insert':
				self::println(sprintf(_r('Inserting item: %s'), $detail->hash));
				self::println("\t" . sprintf(_r('Title: %s'), $detail->title));
				break;
			case 'notice':
				if(!self::$cmdline)
					echo '<h2>' . $detail . '</h2>';
				else
					self::println($detail);
				break;
			case 'error':
				if(!self::$cmdline)
					echo '<span style="color:red">' . sprintf(_r('Error: %s'), $detail) . '</span><br />';
				else
					self::println("\t" . sprintf(_r('Error: %s'), $detail));
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

if(!defined('LILINA_PATH'))
	ItemUpdater::bootstrap();

if(function_exists('add_action'))
	add_action('controller-register', array('ItemUpdater', 'register'), 10, 1);

if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']))
	ItemUpdater::init();