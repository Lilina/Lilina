<?php
/**
 * Base DB adapter
 *
 * @package Lilina
 * @subpackage Database
 */

/**
 * Base DB adapter
 *
 * @package Lilina
 * @subpackage Database
 */
abstract class Lilina_DB_Adapter_Base {
	/**
	 * Convert an object to an array
	 *
	 * Either uses $obj->_db_export($db_options) if it exists, or
	 * get_object_vars() otherwise.
	 *
	 * @param object $obj
	 * @return array Associative array of properties
	 */
	protected static function object_to_array(&$obj, $options) {
		if (method_exists($obj, '_db_export') && is_callable(array($obj, '_db_export'))) {
			return call_user_func(array($obj, '_db_export'), $options);
		}
		else {
			return get_object_vars($obj);
		}
	}
}