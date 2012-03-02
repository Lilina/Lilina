<?php

class Lilina_DB {
	public static function get_adapter($args = null, $class = null) {
		if (empty($class)) {
			$class = get_option('dbdriver', 'Lilina_DB_Adapter_File');
		}

		return new $class($args);
	}
}