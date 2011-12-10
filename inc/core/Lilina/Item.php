<?php

class Lilina_Item extends Lilina_Object {
	public $hash;
	public $timestamp;
	public $title;
	public $content;
	public $summary;
	public $permalink;
	public $metadata;
	public $feed = false;
	public $feed_id;

	public $author = false;
	public $enclosure = false;

	protected $data;

	public function __get($name) {
		if ($name === 'data') {
			return $this->get_data();
		}
	}

	/*public function __set($name, $value) {
		if ($name === 'data') {
			throw new Exception();
		}
	}*/
	
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
		return (!empty($this->metadata->enclosure) || !empty($this->enclosure));
	}
	
	/**
	 * Return the enclosure for the current item
	 *
	 * @since 1.0
	 *
	 * @return string Absolute URL to the enclosure
	 */
	public function get_enclosure() {
		if (!empty($this->metadata->enclosure)) {
			return $this->metadata->enclosure;
		}
		if (!empty($this->enclosure)) {
			return $this->enclosure;
		}
		return false;
	}

	public function get_data($name) {
		// Lilina_Item_Data::get($this->hash)
	}

	public function set_data($name, $value) {
		// Lilina_Item_Data::set($this->hash, $this->data)
	}

	public function get_feed() {
		return Feeds::get_instance()->get($this->feed_id);
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
		else {
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