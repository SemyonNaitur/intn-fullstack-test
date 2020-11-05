<?php

namespace System\Libraries;

class Db
{
	private $dbConfig = [
		'host' => '',
		'dbname' => '',
		'user'	=> '',
		'pass' => '',
	];
	protected \PDO $pdo;

	protected $table = '';
	protected $fields = [];
	protected $columns = [];

	public $debug = false;

	/**
	 * Constructor
	 * 
	 * @param mixed a db config array or PDO instance
	 */
	public function __construct($dbConfig)
	{
		if ($dbConfig instanceof \PDO) {
			$this->setConnection($dbConfig);
		} elseif (is_array($dbConfig)) {
			$this->config = $cfg = array_merge($this->dbConfig, $dbConfig);

			try {
				$this->setConnection(self::PDO($cfg));
			} catch (\PDOException $e) {
				$this->dbException($e);
			}
		} else {
			throw new \Exception('Invalid config array!');
		}

		$this->initFields();
	}

	public static function pdo(array $cfg): \PDO
	{
		$pdo = new \PDO("mysql:host=$cfg[host];dbname=$cfg[dbname]", $cfg['user'], $cfg['pass'], array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
		return $pdo;
	}

	private function initFields()
	{
		$cols = [];
		foreach ($this->fields as $fld => &$params) {
			$col = $params['column'] ?? $fld;
			$params['column'] = $col;
			$cols[$col] = $fld;
		}
		$this->columns = $cols;
	}

	/**
	 * Converts field name to db column name
	 * 
	 * @return string|bool
	 */
	protected function fieldToColumn(string $field)
	{
		return $this->fields[$field]['column'] ?? false;
	}

	/**
	 * Converts db column name to field name
	 * 
	 * @return string|bool
	 */
	protected function columnToField(string $column)
	{
		return $this->columns[$column] ?? false;
	}

	/**
	 * Converts an array of field names to a string for select statements.
	 * format: table_name.column_name AS fieldName
	 * 
	 * @param 	array 	$fields
	 * @return 	string
	 */
	protected function colsAsFields(array $fields = null): string
	{
		$fields ??= array_keys($this->fields);
		$fields = array_map(
			function ($field) {
				if (!($col = $this->fieldToColumn($field))) {
					throw new \Exception("Unknown field: $field");
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
	public function isUnique($value, string $field, $table = ''): bool
	{
		$table = $table ?: $this->table;
		$col = $this->fieldToColumn($field) ?: $field;
		$sql = "SELECT 1 FROM $table WHERE $col=? LIMIT 1";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([$value]);
		return !$stmt->fetchColumn();
	}

	public function beginTransaction()
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

	public function dbException($e)
	{
		if ($e instanceof \PDOException) {
			return ['error' => ($this->debug) ? $e->getMessage() : 'DB Error.'];
		}
		throw $e;
	}

	public function setConnection(\PDO $db): void
	{
		$this->pdo = $db;
	}

	public function getConnection(): \PDO
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
	public function filterFields(array $record, $allowed = []): array
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
