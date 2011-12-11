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
	 *
	 * @param array $options Options array, see source for reference
	 * @return array Rows which match all the conditions
	 */
	public function retrieve($options);

	/**
	 * Update rows in the database
	 *
	 * The $data parameter is a key => value partial data array. It is
	 * merged with the existing data.
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
	 * @param array $data Data array, see source for reference
	 * @param array $options Options array, see source for reference
	 * @return boolean
	 */
	public function update($data, $options);
}