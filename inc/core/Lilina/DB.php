<?php

class Lilina_DB {
	public static function get_adapter($args = null, $class = null) {
		if (empty($class)) {
			$class = 'Lilina_DB_Adapter_File';
		}

		return new $class($args);
	}
}