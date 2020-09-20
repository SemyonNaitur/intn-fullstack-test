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
			$this->pdo = $db_config;
		} elseif (is_array($db_config)) {
			$this->config = $cfg = array_merge($this->db_config, $db_config);

			try {
				$this->pdo = self::PDO($cfg);
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

	public function is_unique($value, string $field, string $table = null)
	{
		$table ??= $this->table;
		$col = $this->field_to_column($field);
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

	public function get_connection()
	{
		return $this->pdo;
	}

	public function filter_fields(array $record, array $allowed = null)
	{
		$allowed ??= array_keys($this->fields);
		return array_filter(
			$record,
			fn ($k) => in_array($k, $allowed),
			ARRAY_FILTER_USE_KEY
		);
	}
}
