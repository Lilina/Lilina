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
	 *    - 'conditions': An array of conditions to apply, each condition is
	 *       an associative array:
	 *        - 'type': Comparsion type (=, !=, <, >, <=, >=)
	 *        - 'key': Column name to compare
	 *        - 'value': Value to compare against
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
}