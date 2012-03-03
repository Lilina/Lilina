<?php

class Lilina_DB {
	protected static $adapter = null;

	public static function &get_adapter($args = null, $class = null) {
		if (!empty(self::$adapter)) {
			return self::$adapter;
		}

		if (empty($class)) {
			$class = 'Lilina_DB_Adapter_File';
		}

		self::$adapter = new $class($args);
		return self::$adapter;
	}

	public static function set_adapter(Lilina_DB_Adapter &$adapter) {
		self::$adapter = $adapter;
	}
}