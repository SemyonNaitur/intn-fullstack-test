<?php
require_once 'Validator.php';

class DBUtil
{
	private $config = [
		'host' => '',
		'dbname' => '',
		'user'	=> '',
		'pass' => '',
	];
	protected $pdo;

	protected $table = '';
	protected $fields = [];

	public $debug = false;

	/**
	 * Constructor
	 * 
	 * @param mixed a db config array or PDO instance
	 */
	public function __construct($config)
	{
		if ($config instanceof PDO) {
			$this->pdo = $config;
		} elseif (is_array($config)) {
			$this->config = $cfg = array_merge($this->config, $config);

			try {
				$this->pdo = self::PDO($cfg);
			} catch (PDOException $e) {
				$this->exception($e);
			}
		} else {
			throw new Exception('Invalid config array!');
		}
	}

	public static function PDO($cfg)
	{
		$pdo = new PDO("mysql:host=$cfg[host];dbname=$cfg[dbname]", $cfg['user'], $cfg['pass'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		return $pdo;
	}

	public static function validate(array $row, array $rules)
	{
		$errors = [];
		$row_valid = true;
		foreach ($rules as $fld_name => $fld_rules) {
			$fld_rules = (is_array($fld_rules)) ? $fld_rules : explode('|', $fld_rules);
			$val = $row[$fld_name] ?? null;
			$errors[$fld_name] = [];

			if (in_array('required', $fld_rules)) {
				if ($val === null) {
					$errors[$fld_name][] = 'Required field';
					$row_valid = false;
					continue;
				}
			}

			foreach ($fld_rules as $rule) {
				$func = trim($rule) . '_rule';
				if ($func == 'required_rule') continue;

				if (!method_exists('Validator', $func)) {
					throw new Exception("Invalid rule: $rule");
				}

				if (($valid = Validator::$func($val)) !== true) {
					$errors[$fld_name][] = $valid;
					$row_valid = false;
				}
			}
		}

		return $row_valid ?: $errors;
	}

	protected function exception($e)
	{
		$err = 'Error';
		if ($e instanceof PDOException) {
			$err = 'DB Error';
		}

		if ($this->debug) {
			$err = $e->getMessage();
		}
		return ['error' => $err];
	}

	public function getConnection()
	{
		return $this->pdo;
	}

	public function filter_fields(array $row, array $allowed = null)
	{
		$allowed ??= array_keys($this->fields);
		return array_filter(
			$row,
			fn ($k) => in_array($k, $allowed),
			ARRAY_FILTER_USE_KEY
		);
	}
}
