<?php

class Lilina_DB {
	public static function &get_adapter($args = null, $class = null) {
		static $adapter = null;

		if (!empty($adapter)) {
			return $adapter;
		}

		if (empty($class)) {
			$class = get_option('dbdriver', 'Lilina_DB_Adapter_File');
		}

		$adapter = new $class($args);
		return $adapter;
	}
}