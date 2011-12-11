<?php

interface Lilina_DB_Adapter {
	/**
	 * Create a new DB handler
	 *
	 * @param array $options Options for the database, as loaded from the DB
	 */
	public function __construct($options);

	/**
	 * Retrieve rows from the database
	 *
	 * The $options parameter specifies an associative array of options:
	 *
	 *    - 'table': The data set name
	 *    - 'fields': The field names to return for each row
	 *    - 'where': An array of conditions to apply, each condition is
	 *       an associative array:
	 *        - 0: Column name to compare
	 *        - 1: Comparsion type (==, !=, ===, !== <, >, <=, >=)
	 *        - 2: Value to compare against
	 *    - 'limit': Maximum number of rows to return
	 *    - 'offset': Row number to start from
	 *    - 'orderby': Associative array:
	 *        - 'key': Key to sort against
	 *        - 'direction': Direction, either asc or desc
	 *        - 'compare': Type of comparison to use (int, str, strcase (case insensitive))
	 *    - 'fetchas': Type to return (array, or class name)
	 *    - 'reindex': For some adapters, we might need to reindex. If so,
	 *       specify which parameter we should get the key from (e.g. MySQL)
	 *
	 * @param array $options Options array, see source for reference
	 * @return array Rows which match all the conditions
	 */
	public function retrieve($options);

	/**
	 * Count rows from the DB
	 *
	 * The $options parameter specifies an associative array of options:
	 *
	 *    - 'table': The data set name
	 *    - 'where': An array of conditions to apply, each condition is
	 *       an associative array:
	 *        - 0: Column name to compare
	 *        - 1: Comparsion type (==, !=, ===, !== <, >, <=, >=)
	 *        - 2: Value to compare against
	 *    - 'limit': Maximum number of rows to return
	 *    - 'offset': Row number to start from
	 *
	 * @param array $options Options array, see source for reference
	 * @return array Row count
	 */
	public function count($options);

	/**
	 * Insert rows into the database
	 *
	 * The $data parameter is a key => value partial data array. It is
	 * merged with the existing data. It can also be an object, and the
	 * public properties will be taken either from the result of
	 * `$data->_db_export()` (if it exists) or from `get_object_vars($data)`
	 *
	 * The $options parameter specifies an associative array of options:
	 *
	 *    - 'table': The data set name
	 *    - 'primary': The primary key name
	 *
	 * @param array|object $data Data array, see source for reference
	 * @param array $options Options array, see source for reference
	 * @return boolean
	 */
	public function insert($data, $options);

	/**
	 * Update rows in the database
	 *
	 * The $data parameter is a key => value partial data array. It is
	 * merged with the existing data. It can also be an object, and the
	 * public properties will be taken either from the result of
	 * `$data->_db_export()` (if it exists) or from `get_object_vars($data)`
	 *
	 * The $options parameter specifies an associative array of options:
	 *
	 *    - 'table': The data set name
	 *    - 'where': An array of conditions to apply, each condition is
	 *       an associative array:
	 *        - 0: Column name to compare
	 *        - 1: Comparsion type (==, !=, ===, !== <, >, <=, >=)
	 *        - 2: Value to compare against
	 *    - 'limit': Maximum number of rows to return
	 *
	 * @param array|object $data Data array, see source for reference
	 * @param array $options Options array, see source for reference
	 * @return boolean
	 */
	public function update($data, $options);
}