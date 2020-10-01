<?php

class DBUtil
{
	private $db_config = [
		'host' => '',
		'dbname' => '',
		'user'	=> '',
		'pass' => '',
	];
	protected $pdo;

	protected $table = '';
	protected $fields = [];
	protected $columns = [];

	public $debug = false;

	/**
	 * Constructor
	 * 
	 * @param mixed a db config array or PDO instance
	 */
	public function __construct($db_config)
	{
		if ($db_config instanceof PDO) {
			$this->set_connection($db_config);
		} elseif (is_array($db_config)) {
			$this->config = $cfg = array_merge($this->db_config, $db_config);

			try {
				$this->set_connection(self::PDO($cfg));
			} catch (PDOException $e) {
				$this->db_exception($e);
			}
		} else {
			throw new Exception('Invalid config array!');
		}

		$this->init_fields();
	}

	public static function PDO($cfg)
	{
		$pdo = new PDO("mysql:host=$cfg[host];dbname=$cfg[dbname]", $cfg['user'], $cfg['pass'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		return $pdo;
	}

	private function init_fields()
	{
		$cols = [];
		foreach ($this->fields as $fld => &$params) {
			$col = $params['column'] ?? $fld;
			$params['column'] = $col;
			$cols[$col] = $fld;
		}
		$this->columns = $cols;
	}

	protected function field_to_column(string $field)
	{
		return $this->fields[$field]['column'] ?? false;
	}

	protected function column_to_field(string $column)
	{
		return $this->columns[$column] ?? false;
	}

	protected function cols_as_fields(array $fields = null)
	{
		$fields ??= array_keys($this->fields);
		$fields = array_map(
			function ($field) {
				if (!($col = $this->field_to_column($field))) {
					throw new Exception("Unknown field: $field");
				}
				return "$this->table.$col AS $field";
			},
			$fields
		);
		return implode(', ', $fields);
	}

	/**
	 * Checks if the value axists in the table
	 * 
	 * @param 	string|number 	$value
	 * @param 	string 			$field field or column name
	 * @param 	string 			$table
	 * @return 	bool
	 */
	public function is_unique($value, string $field, $table = ''): bool
	{
		$table = $table ?: $this->table;
		$col = $this->field_to_column($field) ?: $field;
		$sql = "SELECT 1 FROM $table WHERE $col=? LIMIT 1";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([$value]);
		return !$stmt->fetchColumn();
	}

	public function begin_transaction()
	{
		$this->pdo->beginTransaction();
	}

	public function commit()
	{
		$this->pdo->commit();
	}

	public function rollback()
	{
		$this->pdo->rollback();
	}

	public function db_exception($e)
	{
		if ($e instanceof PDOException) {
			return ['error' => ($this->debug) ? $e->getMessage() : 'DB Error.'];
		}
		throw $e;
	}

	public function set_connection($db)
	{
		$this->pdo = $db;
	}

	public function get_connection()
	{
		return $this->pdo;
	}

	/**
	 * Removes unwanted fields from given record.
	 * 
	 * @param 	array 	$record
	 * @param 	array 	$allowed optional, wanted fields list
	 * @return 	array 	new filtered array
	 */
	public function filter_fields(array $record, $allowed = []): array
	{
		$allowed = $allowed ?: array_keys($this->fields);
		return array_filter(
			$record,
			function ($k) use ($allowed) {
				return in_array($k, $allowed);
			},
			ARRAY_FILTER_USE_KEY
		);
	}
}
