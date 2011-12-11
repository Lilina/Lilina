<?php

class Lilina_Item extends Lilina_Object {
	public $hash;
	public $timestamp;
	public $title;
	public $content;
	public $summary;
	public $permalink;
	//public $feed = false;
	public $feed_id;

	// These are actually public
	protected $author = false;
	protected $enclosure = false;
	protected $metadata = false;

	protected $data;

	public function __get($name) {
		switch ($name) {
			case 'data':
				return $this->get_data();
			case 'enclosure':
				return $this->get_enclosure();
			case 'author':
				return $this->get_author();
		}
	}

	public function __set($name, $value) {
		switch ($name) {
			case 'feed':
				// Simply ignore it
				return;
			case 'author':
				if (is_string($value)) {
					$value = unserialize($value);
				}
				if ($value instanceof Lilina_Author) {
					$this->author = $value;
				}
				else {
					$this->author = Lilina_Author::from_obj($value);
				}
				return;
			case 'enclosure':
				if (is_string($value)) {
					$value = unserialize($value);
				}
				if ($value === null) {
					return;
				}
				if ($value instanceof Lilina_Enclosure) {
					$this->enclosure = $value;
				}
				else {
					$this->enclosure = Lilina_Enclosure::from_obj($value);
				}
				return;
			case 'metadata':
				if (is_string($value)) {
					$value = unserialize($value);
				}
				$this->metadata = $value;
				return;
		}

		throw new Exception($name . ' is not settable');
	}
	
	/**
	 * Check whether the current item has an enclosure or not
	 *
	 * Checks to make sure an item has an enclosure and that that enclosure
	 * has a link to use.
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public function has_enclosure() {
		return !empty($this->enclosure);
	}
	
	/**
	 * Return the enclosure for the current item
	 *
	 * @since 1.0
	 *
	 * @return string Absolute URL to the enclosure
	 */
	public function get_enclosure() {
		if (!empty($this->enclosure)) {
			return $this->enclosure;
		}
		if (!empty($this->metadata['enclosure']) && $this->metadata['enclosure'] !== false) {
			$enclosure = new Lilina_Enclosure();
			$enclosure->url = $this->metadata['enclosure'];
			$enclosure->type = $this->metadata['enclosure_data']['type'];
			$enclosure->length = $this->metadata['enclosure_data']['length'];
			return $enclosure;
		}
		return false;
	}

	public function get_feed() {
		return Feeds::get_instance()->get($this->feed_id);
	}

	public function get_author() {
		return $this->author;
	}

	public function get_data($name) {
		// Lilina_Item_Data::get($this->hash)
	}

	public function set_data($name, $value) {
		// Lilina_Item_Data::set($this->hash, $this->data)
	}

	public function _db_export($options) {
		$vars = array('hash', 'timestamp', 'title', 'content', 'summary',
			'permalink', 'feed_id', 'author', 'metadata');

		$data = array();
		foreach ($vars as $var) {
			if (is_object($this->$var) || is_array($this->$var)) {
				$data[$var] = serialize($this->$var);
			}
			else {
				$data[$var] = $this->$var;
			}
		}
		$data['enclosure'] = serialize($this->get_enclosure());

		return $data;
	}

	// -- Static Methods -- \\

	public static function &from_obj($obj) {
		return parent::_from_obj(__CLASS__, $obj);
	}

	public static function from_sp($item, $feed) {
		$new_item = new Lilina_Item();
		$new_item->hash      = sha1($item->get_id());
		$new_item->title     = $item->get_title();
		$new_item->content   = $item->get_content();
		$new_item->summary   = $item->get_description();
		$new_item->permalink = $item->get_permalink();

		$date = $item->get_date('U');
		if ($date === 0 || $date === false || $date === null) {
			$date = self::default_date($item);
		}
		$new_item->timestamp = $date;

		if ($enclosure = $item->get_enclosure()) {
			$new_item->enclosure = new Lilina_Enclosure();
			$new_item->enclosure->url = $enclosure->get_link();
			$new_item->enclosure->type = $enclosure->get_real_type();
			$new_item->enclosure->length = $enclosure->get_length();
		}

		if ($author = $item->get_author()) {
			$new_item->author = new Lilina_Author();
			$new_item->author->name = $item->get_author()->get_name();
			$new_item->author->url = $item->get_author()->get_link();
		}

		if(!empty($feed))
			$new_item->feed_id = $feed;

		return $new_item;
	}

	protected static function default_date(&$item) {
		$date = $item->get_feed()->get_channel_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'pubDate');
		$date = strtotime($date[0]['data']);

		if ($date !== 0 && $date !== false && $date !== null) {
			return $date;
		}

		$date = $item->get_feed()->get_channel_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'lastBuildDate');
		$date = strtotime($date[0]['data']);

		if ($date !== 0 && $date !== false && $date !== null) {
			return $date;
		}

		return 0;
	}
}