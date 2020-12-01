<?php

namespace System\Libraries;

class Db
{
	protected $db_config = [
		'host' => '',
		'dbname' => '',
		'user'	=> '',
		'pass' => '',
	];
	protected \PDO $pdo;
	protected string $sql = '';
	protected \PDOStatement $stmt;
	protected bool $executed = false;

	/**
	 * Constructor
	 * 
	 * @param mixed a db config array or PDO instance
	 */
	public function __construct($config)
	{
		if ($config instanceof \PDO) {
			$this->setPdo($config);
		} elseif (is_array($config)) {
			$this->config = $cfg = array_merge($this->db_config, $config);

			try {
				$this->setPdo(self::pdo($cfg));
			} catch (\PDOException $e) {
				$this->exception($e);
			}
		} else {
			throw new \Exception('Invalid config array!');
		}
	}

	public static function pdo(array $config): \PDO
	{
		$dsn = "mysql:host=$config[host];dbname=$config[dbname]";
		$opts = [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"];
		$pdo = new \PDO($dsn, $config['user'], $config['pass'], $opts);
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
		return $pdo;
	}

	public function exception($e)
	{
		if ($e instanceof \PDOException) {
			return ['error' => (app_config('debug')) ? $e->getMessage() : 'DB Error.'];
		}
		throw $e;
	}

	/**
	 * Checks if the value axists in the table
	 * 
	 * @param 	string|number 	$value
	 * @param 	string 			$column field or column name
	 * @param 	string 			$table
	 * @return 	bool
	 */
	public function isUnique($value, string $column, $table = ''): bool
	{
		$sql = "SELECT 1 FROM $table WHERE $column=? LIMIT 1";
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

	public function lastInsertId()
	{
		$this->pdo->lastInsertId();
	}

	protected function setPdo(\PDO $con): void
	{
		$this->pdo = $con;
	}

	public function getPdo(): \PDO
	{
		return $this->pdo;
	}
}
