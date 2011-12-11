<?php
/**
 * MySQL DB adapter
 *
 * @package Lilina
 * @subpackage Database
 */

/**
 * MySQL DB adapter
 *
 * @package Lilina
 * @subpackage Database
 */
class Lilina_DB_Adapter_MySQL implements Lilina_DB_Adapter {
	/**
	 * PDO handle
	 */
	protected $db;

	/**
	 * Table prefix
	 */
	protected $prefix;

	/**
	 * Create new MySQL DB adapter
	 *
	 * @param array $options Associative array, with keys 'host', 'db', 'user' and 'pass'
	 */
	public function __construct($options) {
		$defaults = array(
			'prefix' = 'lilina_'
		);
		$options = array_merge($defaults, $options);

		// This is probably unsafe
		$dsn = 'mysql:host=' . $options['host'] . ';dbname=' . $options['db'];
		$this->db = new PDO($dsn, $options['user'], $options['pass']);
		$this->prefix = $options['prefix'];

		// We need this so that `int`s are fetched as integers, etc
		// when using mysqlnd
		$this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	/**
	 * Retrieve rows from the database
	 *
	 * @param array $options Options array, see Lilina_DB
	 * @return array Rows which match all the conditions
	 */
	public function retrieve($options) {
		$default = array(
			'table' => null,
			'fields' => null,
			'conditions' => array(),
			'limit' => null,
			'offset' => 0,
			'orderby' => array(),
			'fetchas' => 'array'
		);
		$options = array_merge($default, $options);

		$options['table'] = $this->prefix . $options['table'];

		if ($options['fields'] === null) {
			$fields = '*';
		}
		else {
			$fields = $options['fields'];
			$fields = implode(', ', $fields);
		}
		$sql = 'SELECT ' . $fields . ' FROM ' . $options['table'];

		// Check conditions
		$values = array();
		if (!empty($options['conditions'])) {
			$sql .= ' WHERE (';
			foreach ($options['conditions'] as $condition) {
				$conditions[] = $condition['key'] . ' ' . $condition['type'] . ' :' . $condition['key'];
				$values[$condition['key']] = $condition['value'];
			}
			$sql .= implode(' AND ', $conditions) . ')';
		}

		// Order our data
		if ($options['orderby'] !== null && !empty($options['orderby']['key'])) {
			$sql .= ' ORDER BY ' . $this->db->quote($options['orderby']['key']);
			if (!empty($options['orderby']['direction']) && $options['orderby']['direction'] === 'desc') {
				 $sql .= ' DESC';
			}
		}

		// Cut down to just what we need
		if ($options['limit'] !== null) {
			if ($options['offset'] !== 0) {
				$sql .= ' LIMIT ' . $options['limit'] . ' OFFSET ' . $options['offset'];
			}
			else {
				$sql .= ' LIMIT ' . $options['limit'];
			}
		}
		elseif ($options['offset'] !== 0) {
			// absurdly large number, since we can't use an offset otherwise
			$sql .= ' LIMIT 18446744073709551615 OFFSET ' . $options['offset'];
		}

		$sql .= ';';
		$stmt = $this->db->prepare($sql);

		if (!empty($values)) {
			foreach ($values as $key => $value) {
				$stmt->bindValue(':' . $key, $value);
			}
		}

		if (!$stmt->execute()) {
			$error = $stmt->errorInfo();
			throw new Lilina_DB_Exception($error[2]);
		}

		// We have to do this because PDO::FETCH_CLASS doesn't call __set()
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if ($options['fetchas'] !== 'array') {
			foreach ($data as $id => $row) {
				$data[$id] = new $options['fetchas']();
				foreach ($row as $k => $v) {
					$data[$id]->$k = $v;
				}
			}
		}

		return $data;
	}
}