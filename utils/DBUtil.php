<?php

class DBUtil
{
	private $config = [
		'host' => '',
		'dbname' => '',
		'user'	=> '',
		'pass' => '',
	];
	protected $pdo;
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
				$this->pdo = new PDO("mysql:host=$cfg[host];dbname=$cfg[dbname]", $cfg['user'], $cfg['pass'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
				$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			} catch (PDOException $e) {
				$this->pdo_exception($e);
			}
		} else {
			throw new Exception('Invalid config array!');
		}
	}

	protected function pdo_exception($e)
	{
		return ['error' => ($this->debug) ? $e->getMessage() : 'DB Error.'];
	}

	public function getConnection()
	{
		return $this->pdo;
	}
}
