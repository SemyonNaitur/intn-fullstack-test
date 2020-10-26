<?php

use System\DB;

class User extends DB
{
    protected $table = 'users';
    protected $primary_key = 'id';
    protected $fields = [
        'id' => [
            'type' => 'integer',
        ],
        'name' => [
            'type' => 'string',
            'required' => true,
        ],
        'email' => [
            'type' => 'string',
            'required' => true,
        ],
        'updatedAt' => [
            'column' => 'updated_at',
            'type' => 'date',
        ],
        'createdAt' => [
            'column' => 'created_at',
            'type' => 'date',
        ],
    ];

    public function __construct($db_config)
    {
        parent::__construct($db_config);
    }

    public function insert_batch($data)
    {
        try {
            $ret = ['inserted' => 0, 'error' => ''];
            $pdo = $this->pdo;
            $tbl = $this->table;

            $sql = "INSERT INTO $tbl (id,name,email) VALUES (:id,:name,:email)";
            $stmt = $pdo->prepare($sql);
            $pdo->beginTransaction();
            foreach ($data as $record) {
                $record = $this->filterFields($record);
                $stmt->execute($record);
            }
            $pdo->commit();
            $ret['inserted'] = count($data);
            return $ret;
        } catch (PDOException $e) {
            $pdo->rollback();
            return $this->dbException($e);
        }
    }

    public function create(array $record)
    {
        try {
            $ret = ['record' => null, 'error' => ''];
            $pdo = $this->pdo;
            $tbl = $this->table;
            $record = $this->filterFields($record, ['name', 'email']);

            $sql = "INSERT INTO $tbl (name,email) VALUES (:name,:email)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($record);
            $record['id'] = $pdo->lastInsertId();
            $ret['record'] = $record;
            return $ret;
        } catch (PDOException $e) {
            return $this->dbException($e);
        }
    }
}
