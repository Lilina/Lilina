<?php

class Lilina_Updater_PluginInfo {
	/**
	 * Download URL (zipped package)
	 * @var string
	 */
	public $download;

	/**
	 * Displayable name
	 * @var string
	 */
	public $name;

	/**
	 * ID
	 * @var string
	 */
	public $id;

	/**
	 * Version string
	 * @var string
	 */
	public $version;

	/**
	 * Information page URL
	 * @var string
	 */
	public $url;

	public function __construct($id) {
		$this->id = $id;
	}
}