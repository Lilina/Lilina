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
class Lilina_DB_Adapter_MySQL implements Lilina_DB_Adapter extends Lilina_Adapter_Base {
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
			'prefix' => 'lilina_'
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
			'where' => array(),
			'limit' => null,
			'offset' => 0,
			'orderby' => array(),
			'fetchas' => 'array'
		);
		$options = array_merge($default, $options);

		if (empty($options['table'])) {
			throw new Lilina_DB_Exception('Table must be specified');
		}
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
		if (!empty($options['where'])) {
			$where = self::build_where($options['where']);
			$sql .= $where[0];
			$values = $where[1];
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

	/**
	 * Insert rows into the database
	 *
	 * @param array|object $data Data array, see source for reference
	 * @param array $options Options array, see source for reference
	 * @return boolean
	 */
	public function insert($data, $options) {
		$default = array(
			'table' => null,
			'primary' => null,
		);
		$options = array_merge($default, $options);

		if (empty($options['table'])) {
			throw new Lilina_DB_Exception('Table must be specified');
		}
		if (empty($options['primary'])) {
			throw new Lilina_DB_Exception('Primary key must be specified for insert');
		}

		if (is_object($data)) {
			$data = self::object_to_array($data);
		}

		$sql = 'INSERT INTO ' . $options['table'] . ' SET ';
		$fields = array();
		foreach ($data as $key => $value) {
			$fields[] = '`' . $key . '` = :' . $key;
		}
		$sql .= implode(', ', $fields);

		$stmt = $this->db->prepare($sql);
		foreach ($data as $key => $value) {
			$stmt->bindValue(':' . $key, $value);
		}

		if (!$stmt->execute()) {
			$error = $stmt->errorInfo();
			throw new Lilina_DB_Exception($error[2]);
		}

		return true;
	}

	/**
	 * Update rows in the database
	 *
	 * @param array $data Data array, see source for reference
	 * @param array $options Options array, see source for reference
	 * @return boolean
	 */
	public function update($data, $options) {
		$default = array(
			'table' => null,
			'where' => array(),
			'limit' => null,
		);
		$options = array_merge($default, $options);

		if (empty($options['table'])) {
			throw new Lilina_DB_Exception('Table must be specified');
		}
		if (empty($options['where'])) {
			throw new Lilina_DB_Exception('Condition must be specified for update');
		}

		if (is_object($data)) {
			$data = self::object_to_array($data);
		}
		if (!is_array($data)) {
			throw new Lilina_DB_Exception('Data must be an object or array');
		}

		$sql = 'UPDATE ' . $options['table'] . ' SET ';
		$fields = array();
		foreach ($data as $key => $value) {
			$fields[] = '`' . $key . '` = :' . $key;
		}
		$sql .= implode(', ', $fields);

		if (!empty($options['where'])) {
			$where = self::build_where($options['where']);
			$sql .= $where[0];
			$data = array_merge($data, $where[1]);
		}

		if ($options['limit'] !== null) {
			$sql .= ' LIMIT ' . $options['limit'];
		}

		$stmt = $this->db->prepare($sql);

		foreach ($data as $key => $value) {
			$stmt->bindValue(':' . $key, $value);
		}

		if (!$stmt->execute()) {
			$error = $stmt->errorInfo();
			throw new Lilina_DB_Exception($error[2]);
		}

		return true;
	}

	protected function build_where($where) {
		$sql = ' WHERE (';
		$conditions = array();
		foreach ($where as $condition) {
			switch ($condition[1]) {
				case '==':
				case '===':
					$condition[1] = '=';
					break;
				case '!=':
				case '!==':
					$condition[1] = '!=';
					break;
			}
			$conditions[] = $condition[0] . ' ' . $condition[1] . ' :' . $condition[0];
			$values[$condition[0]] = $condition[2];
		}
		$sql .= implode(' AND ', $conditions) . ')';

		return array($sql, $values);
	}
}