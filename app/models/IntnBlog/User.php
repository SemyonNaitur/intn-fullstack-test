<?php

namespace App\Models\IntnBlog;

use System\Core\Model;

class User extends Model
{
    protected $table = 'intn_users';
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

    public function insertBatch($data)
    {
        try {
            $ret = ['inserted' => 0, 'error' => ''];
            $pdo = $this->db->getPdo();
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
        } catch (\PDOException $e) {
            $pdo->rollback();
            return $this->db->exception($e);
        }
    }

    public function create(array $record)
    {
        try {
            $ret = ['record' => null, 'error' => ''];
            $pdo = $this->db->getPdo();
            $tbl = $this->table;
            $record = $this->filterFields($record, ['name', 'email']);

            $sql = "INSERT INTO $tbl (name,email) VALUES (:name,:email)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($record);
            $record['id'] = $pdo->lastInsertId();
            $ret['record'] = $record;
            return $ret;
        } catch (\PDOException $e) {
            return $this->db->exception($e);
        }
    }
}
