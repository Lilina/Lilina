<?php

class Lilina_DB_Adapter_File extends Lilina_DB_Adapter_Base implements Lilina_DB_Adapter {
	protected $directory;
	protected $options;
	protected $tables = array();

	/**
	 * Create new file DB adapter
	 *
	 * @param array $options String to directory containing files
	 */
	public function __construct($options) {
		$this->directory = $options;
	}

	/**
	 * Load data from file
	 *
	 * @param string $table Table name used as filename
	 * @return array
	 */
	protected function load($table) {
		if (!empty($this->tables[$table])) {
			return $this->tables[$table];
		}

		$file = $this->directory . DIRECTORY_SEPARATOR . $table . '.data';
		$this->tables[$table] = json_decode(file_get_contents($file), true);
		return $this->tables[$table];
	}

	/**
	 * Save data to file
	 *
	 * @param string $table Table name used as filename
	 * @param array $data
	 * @return boolean
	 */
	protected function save($table, $data) {
		$this->tables[$table] = $data;
		return true;
	}

	/**
	 * Retrieve rows from the database
	 *
	 * @param array $options Options array, see source for reference
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
			throw new Lilina_DB_Exception('Table must be specified', 'db.general.missingtable');
		}

		$data = $this->load($options['table']);

		// Check conditions
		if (!empty($options['where'])) {
			foreach ($options['where'] as $condition) {
				$this->temp = $condition;
				$data = array_filter($data, array($this, 'where_filter'));
				$this->temp = null;
			}
		}

		// Order our data
		if ($options['orderby'] !== null && !empty($options['orderby']['key'])) {
			$this->temp = $options['orderby']['key'];
			if (empty($options['orderby']['compare'])) {
				$options['orderby']['compare'] = 'str';
			}

			switch ($options['orderby']['compare']) {
				case 'str':
					uasort($data, array($this, 'order_str'));
					break;
				case 'strcase':
					uasort($data, array($this, 'order_strcase'));
					break;
				case 'int':
					uasort($data, array($this, 'order_int'));
					break;
			}
			$this->temp = null;

			if (!empty($options['orderby']['direction']) && $options['orderby']['direction'] === 'desc') {
				 $data = array_reverse($data);
			}
		}

		// Cut down to just what we need
		if ($options['limit'] !== null) {
			$data = array_slice($data, $options['offset'], $options['limit'], true);
		}
		elseif ($options['offset'] !== null) {
			$data = array_slice($data, $options['offset'], null, true);
		}

		// Finally, filter fields we don't want
		if ($options['fields'] !== null) {
			$this->temp = $options['fields'];
			$data = array_map(array($this, 'fields'), $data);
			$this->temp = null;

			// This removes all the rows which have missing fields
			$data = array_filter($data);
		}

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
	 * Count rows from the DB
	 *
	 * @param array $options Options array, see source for reference
	 * @return array Row count
	 */
	public function count($options) {
		$default = array(
			'table' => null,
			'where' => array(),
			'limit' => null,
			'offset' => 0
		);
		$options = array_merge($default, $options);
		if (empty($options['table'])) {
			throw new Lilina_DB_Exception('Table must be specified', 'db.general.missingtable');
		}

		$data = $this->load($options['table']);

		// Check conditions
		if (!empty($options['where'])) {
			foreach ($options['where'] as $condition) {
				$this->temp = $condition;
				$data = array_filter($data, array($this, 'where_filter'));
				$this->temp = null;
			}
		}

		// Cut down to just what we need
		if ($options['limit'] !== null) {
			$data = array_slice($data, $options['offset'], $options['limit']);
		}
		elseif ($options['offset'] !== null) {
			$data = array_slice($data, $options['offset']);
		}

		return count($data);
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
			throw new Lilina_DB_Exception('Table must be specified', 'db.general.missingtable');
		}
		if (empty($options['primary'])) {
			throw new Lilina_DB_Exception('Primary key must be specified for insert', 'db.insert.missingprimary');
		}

		if (is_object($data)) {
			$data = self::object_to_array($data, $options);
		}
		if (!is_array($data)) {
			throw new Lilina_DB_Exception('Data must be an object or array', 'db.general.datatypewrong');
		}

		$primary = $data[$options['primary']];

		$current = $this->load($options['table']);
		if (isset($current[$primary])) {
			throw new Lilina_DB_Exception('Duplicate entry', 'db.insert.duplicate');
		}
		$current[$primary] = $data;

		$this->save($options['table'], $current);

		return true;
	}

	/**
	 * Update rows in the database
	 *
	 * @param array|object $data Data array, see source for reference
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
			throw new Lilina_DB_Exception('Table must be specified', 'db.general.missingtable');
		}
		if (empty($options['where'])) {
			throw new Lilina_DB_Exception('Condition must be specified for update', 'db.update.missingwhere');
		}

		if (is_object($data)) {
			$data = self::object_to_array($data, $options);
		}
		if (!is_array($data)) {
			throw new Lilina_DB_Exception('Data must be an object or array', 'db.general.datatypewrong');
		}

		$current = $this->load($options['table']);

		$actual = $current;
		foreach ($options['where'] as $condition) {
			$this->temp = $condition;
			$actual = array_filter($actual, array($this, 'where_filter'));
			$this->temp = null;
		}

		$actual = array_keys($actual);
		if ($options['limit'] !== null) {
			$actual = array_slice($actual, 0, $options['limit']);
		}

		foreach ($actual as $key) {
			$current[$key] = array_merge($current[$key], $data);
		}

		$this->save($options['table'], $current);

		return true;
	}

	/**
	 * Filters down to just the fields we want
	 *
	 * Also removes any rows which are missing the fields we want
	 * @param array $row
	 * @return array|boolean Returns the filtered array, or false if the row is missing fields
	 */
	protected function fields($row) {
		$keys = array_fill_keys($this->temp, true);
		$row = array_intersect_key($row, $keys);
		$missing = array_diff_key($keys, $row);

		// Remove any rows with missing fields
		if (!empty($missing)) {
			var_dump($missing);
			return false;
		}

		return $row;
	}

	/**
	 * Order for an integer value
	 *
	 * @see usort()
	 * @param array $a
	 * @param array $b
	 * @return integer
	 */
	protected function order_int($a, $b) {
		return $a[$this->temp] - $b[$this->temp];
	}

	/**
	 * Order for a string value
	 *
	 * @see usort()
	 * @param array $a
	 * @param array $b
	 * @return integer
	 */
	protected function order_str($a, $b) {
		return strcmp($a[$this->temp], $b[$this->temp]);
	}

	/**
	 * Order for a string value (case-insensitive)
	 *
	 * @see usort()
	 * @param array $a
	 * @param array $b
	 * @return integer
	 */
	protected function order_strcase($a, $b) {
		return strcasecmp($a[$this->temp], $b[$this->temp]);
	}

	protected function where_filter($row) {
		list($key, $op, $val) = $this->temp;
		switch ($op) {
			case '==':
				return $row[$key] == $val;
			case '===':
				return $row[$key] === $val;
			case '!=':
				return $row[$key] != $val;
			case '!==':
				return $row[$key] !== $val;
			case '>':
				return $row[$key] > $val;
			case '>=':
				return $row[$key] >= $val;
			case '<':
				return $row[$key] <= $val;
			case '<=':
				return $row[$key] <= $val;
			default:
				throw new Lilina_DB_Exception('Operator not valid');
		}
	}
}