<?php

class Lilina_Feed {
	public $feed;
	public $url;
	public $id;
	public $name;
	public $cat;
	public $icon;

	protected $sp = null;

	public function get_items($start = 0, $limit = 0) {
		$args = array(
			'where' => array(
				array('feed_id', '==', $this->id)
			)
		);
		$start = (int) $start;
		$limit = (int) $limit;
		if ($start !== 0) {
			$args['offset'] = $start;
		}
		if ($limit !== 0) {
			$args['limit'] = $limit;
		}

		Lilina_Items::get_instance()->query($args);

		return Lilina_Items::get_instance()->get_items();
	}

	public function update() {
		if (!empty($this->sp)) {
			return ItemUpdater::process_single($this, $this->sp);
		}
		else {
			return ItemUpdater::process_single($this);
		}
	}

	public function _db_export($options) {
		$vars = array('feed', 'url', 'id', 'name', 'cat', 'icon');

		$data = array();
		foreach ($vars as $var) {
			if (is_object($this->$var) || is_array($this->$var)) {
				$data[$var] = serialize($this->$var);
			}
			else {
				$data[$var] = $this->$var;
			}
		}

		return $data;
	}

	/**
	 * Add a new feed to the database
	 *
	 * Adds the specified feed name and URL to the database. If no name is set
	 * by the user, it fetches one from the feed. If the URL specified is a HTML
	 * page and not a feed, it lets SimplePie do autodiscovery and uses the XML
	 * url returned.
	 *
	 * @param string $url URL to feed or website (if autodiscovering)
	 * @param string $name Title/Name of feed
	 * @param string $cat Category to add feed to
	 * @return Lilina_Feed
	 */
	public static function create($url, $name = '', $cat = 'default') {
		if (empty($url)) {
			throw new Exception(_r("Couldn't add feed: No feed URL supplied"), Errors::get_code('admin.feeds.no_url'));
		}

		if(!preg_match('#https|http|feed#', $url)) {
			if(strpos($url, '://')) {
				throw new Exception(_r('Unsupported URL protocol'), Errors::get_code('admin.feeds.protocol_error'));
			}

			$url = 'http://' . $url;
		}

		$reporting = error_reporting();
		error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR);
		require_once(LILINA_INCPATH . '/contrib/simplepie.class.php');
		$sp = new SimplePie();
		$sp->set_stupidly_fast(true);
		$sp->set_cache_location(get_option('cachedir'));
		$sp->set_feed_url($url);
		$sp->set_file_class('Lilina_SimplePie_File');
		$sp->init();
		$error = $sp->error();

		if (!empty($error)) {
			throw new Exception(
				sprintf(_r( "Couldn't add feed: %s is not a valid URL or the server could not be accessed. Additionally, no feeds could be found by autodiscovery. (%s)" ), $url, $error ),
				Errors::get_code('admin.feeds.invalid_url')
			);
		}

		$feed = new Lilina_Feed();
		$feed->url = $sp->get_link();
		$feed->id = sha1($feed->url);
		$feed->name = $name;
		$feed->cat = $cat;
		$feed->icon = self::discover_favicon($sp, $id);
		$feed->feed = $sp->subscribe_url();

		if (empty($feed->name)) {
			$feed->name = $sp->get_title();
		}

		$feed->sp = $sp;

		error_reporting($reporting);

		return $feed;
	}

	/**
	 * Find the feed's icon
	 *
	 * @param SimplePie $feed SimplePie object to retrieve logo for
	 * @return string URL to feed icon
	 */
	protected static function discover_favicon($feed, $id) {
		if ($return = $feed->get_channel_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'icon')) {
			$favicon = SimplePie_Misc::absolutize_url($return[0]['data'], $feed->get_base($return[0]));
		}
		elseif (($url = $feed->get_link()) !== null && preg_match('/^http(s)?:\/\//i', $url)) {
			$filename = $id . '.ico';
			$favicon = SimplePie_Misc::absolutize_url('/favicon.ico', $url);
		}
		else {
			return false;
		}

		$cache = new DataHandler(get_option('cachedir'));
		$file = Lilina_HTTP::get($favicon, array('X-Forwarded-For' => $_SERVER['REMOTE_ADDR']));

		if ($file->success && strlen($file->body) > 0) {
			$sniffer = new $feed->content_type_sniffer_class($file);
			if (substr($sniffer->get_type(), 0, 6) === 'image/') {
				$body = array('type' => $sniffer->get_type(), 'body' => $file->body);
				return $cache->save($filename, serialize($body));
			}
			// not an image
			elseif (($type = $sniffer->unknown()) !== false && substr($type, 0, 6) === 'image/') {
				$body = array('type' => $type, 'body' => $file->body);
				return $cache->save($filename, serialize($body));
			}
		}
		return false;
	}
}