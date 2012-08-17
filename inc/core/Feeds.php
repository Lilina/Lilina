<?php
/**
 * Feed management class
 *
 * @package Lilina
 * @subpackage Administration
 */

/**
 * Feed management class
 *
 * @deprecated
 * @package Lilina
 * @subpackage Administration
 */
class Feeds {
	protected static $instance;
	protected $feeds;
	protected $file;

	public function __construct() {
		$this->file = new DataHandler(LILINA_CONTENT_DIR . '/system/config/');
		$this->load();
	}

	public static function get_instance($sp = null) {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Feeds($sp);
		}
		return self::$instance;
	}

	private function __clone() {}

	/**
	 * Add a new feed to the database
	 *
	 * Adds the specified feed name and URL to the database. If no name is set
	 * by the user, it fetches one from the feed. If the URL specified is a HTML
	 * page and not a feed, it lets SimplePie do autodiscovery and uses the XML
	 * url returned.
	 *
	 * @since 1.0
	 *
	 * @param string $url URL to feed or website (if autodiscovering)
	 * @param string $name Title/Name of feed
	 * @param string $cat Category to add feed to
	 * @return bool True if succeeded, false if failed
	 */
	public function add($url, $name = '', $cat = 'default') {
		$feed = Lilina_Feed::create($url, $name);
		$result = Lilina_Feeds::get_instance()->insert($feed);

		return array('msg' => sprintf( _r('Added feed "%1$s"'), $feed->name ), 'id' => $feed->id);;
	}

	public function get($id) {
		return ailina_Feeds::get_instance()->get($id);
	}

	public function getAll() {
		return Lilina_Feeds::get_instance()->get_items();
	}

	/**
	 * Change a feed's properties
	 *
	 * @param string $id ID of the feed to change
	 * @return bool
	 */
	public function update($id, $data = array()) {
		$feed = Lilina_Feeds::get_instance()->get($id);

		if (!empty($data['name'])) {
			$feed->name = $data['name'];
		}

		if (!empty($data['feed'])) {
			$feed->feed = $data['feed'];
		}

		if (!empty($data['url'])) {
			$feed->url = $data['url'];
		}

		Lilina_Feeds::get_instance()->update($feed);
		return sprintf(_r('Changed "%s" (#%d)'), $feed->name, $feed->id);
	}

	/**
	 * Remove a feed
	 *
	 * @param string $id ID of the feed to remove
	 * @return bool
	 */
	public function delete($id) {
		$feed = Lilina_Feeds::get_instance()->get($id);
		Lilina_Feeds::get_instance()->delete($id);
		return sprintf(
			_r('Removed "%1$s" &mdash; <a href="%2$s">Undo</a>?'),
			$feed->name,
			'feeds.php?action=add&add_name=' . urlencode($feed->name) . '&add_url=' . urlencode($feed->feed) . '&id=' . urlencode($feed->id)
		);
	}

	/**
	 * Load feeds from database
	 *
	 * @return array
	 */
	protected function load() {
		$data = $this->file->load('feeds.json');
		if($data === null) {
			$data = $this->upgrade();
		}
		else
			$data = json_decode($data, true);

		$this->feeds = $data;
		if(get_option('feeds_version', 0) !== 2) {
			$new_feeds = array();

			// Crappy workaround
			set_time_limit(count($this->feeds) * 10);
			foreach($this->feeds as $id => $feed) {
				$new_feeds[$id] = $this->upgrade_single($feed);
			}

			$this->feeds = $new_feeds;
			$this->save();
			update_option('feeds_version', 2);
		}
	}

	protected function upgrade() {
		$data = $this->file->load('feeds.data');
		if($data === null) {
			return array();
		}
		$data = unserialize(base64_decode($data));
		$new_data = array();
		foreach($data['feeds'] as $feed) {
			$id = sha1($feed['feed']);

			$new_data[$id] = array_merge($feed, array('id' => $id));
		}
		$this->feeds = $new_data;
		$this->save();

		return $new_data;
	}

	protected function upgrade_single($feed) {
		require_once(LILINA_INCPATH . '/contrib/simplepie.class.php');
		$sp = new SimplePie();
		$sp->set_useragent(LILINA_USERAGENT . ' SimplePie/' . SIMPLEPIE_BUILD);
		$sp->set_stupidly_fast(true);
		$sp->set_cache_location(get_option('cachedir'));
		$sp->set_feed_url($feed['feed']);
		$sp->init();
		if(!isset($feed['icon'])) {
			$feed['icon'] = $sp->get_favicon();
		}

		return $feed;
	}

	/**
	 * Save feeds to database
	 *
	 * @return bool True if feeds were successfully saved, false otherwise
	 */
	protected function save() {
		return $this->file->save('feeds.json', json_encode($this->feeds));
	}
}