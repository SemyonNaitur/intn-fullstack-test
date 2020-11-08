<?php

namespace App\Models;

use System\Core\Model;

class Post extends Model
{
    protected $table = 'posts';
    protected $primary_key = 'id';
    protected $fields = [
        'id' => [
            'type' => 'integer',
        ],
        'userId' => [
            'column' => 'user_id',
            'type' => 'integer',
            'required' => true,
        ],
        'title' => [
            'type' => 'string',
            'required' => true,
        ],
        'body' => [
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

            $sql = "INSERT INTO $tbl (id,user_id,title,body) VALUES (:id,:userId,:title,:body)";
            $stmt = $pdo->prepare($sql);
            $pdo->beginTransaction();
            foreach ($data as $row) {
                $row = $this->filterFields($row);
                $stmt->execute($row);
            }
            $pdo->commit();
            $ret['inserted'] = count($data);
            return $ret;
        } catch (\PDOException $e) {
            $pdo->rollback();
            return $this->db->Exception($e);
        }
    }

    public function create(array $record)
    {
        try {
            $ret = ['record' => null, 'error' => ''];
            $pdo = $this->db->getPdo();
            $tbl = $this->table;
            $record = $this->filterFields($record, ['userId', 'title', 'body']);

            $sql = "INSERT INTO $tbl (user_id,title,body) VALUES (:userId,:title,:body)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($record);
            $record['id'] = $pdo->lastInsertId();
            $ret['record'] = $record;
            return $ret;
        } catch (\PDOException $e) {
            return $this->db->exception($e);
        }
    }

    public function searchById($id)
    {
        try {
            $ret = ['row' => null, 'error' => ''];
            $pdo = $this->db->getPdo();
            $tbl = $this->table;
            $pk = $this->primary_key;

            $select = $this->colsAsFields();
            $sql = "SELECT $select FROM $tbl WHERE $pk=? LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $ret['row'] = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $ret;
        } catch (\PDOException $e) {
            return $this->db->exception($e);
        }
    }

    public function searchByUserId($user_id)
    {
        try {
            $ret = ['result' => null, 'error' => ''];
            $pdo = $this->db->getPdo();
            $tbl = $this->table;

            $select = $this->colsAsFields();
            $sql = "SELECT $select FROM $tbl WHERE user_id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            $ret['result'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $ret;
        } catch (\PDOException $e) {
            return $this->db->exception($e);
        }
    }

    public function searchByContent($search)
    {
        try {
            $ret = ['result' => null, 'error' => ''];
            $pdo = $this->db->getPdo();
            $tbl = $this->table;

            $select = $this->colsAsFields();
            $sql = "SELECT $select FROM $tbl WHERE title LIKE :search OR body LIKE :search";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['search' => "%$search%"]);
            $ret['result'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $ret;
        } catch (\PDOException $e) {
            return $this->db->exception($e);
        }
    }

    public function userStats()
    {
        try {
            $ret = ['result' => null, 'error' => ''];
            $pdo = $this->db->getPdo();
            $tbl = $this->table;

            $select = $this->colsAsFields(['userId']);
            $sql  = "SELECT $select, ROUND((COUNT(*) / 12), 2) AS monthlyAvg, ROUND((COUNT(*) / (365/7)), 2) AS weeklyAvg ";
            $sql .= "FROM $tbl GROUP BY user_id, YEAR(created_at) ORDER BY user_id";
            $ret['result'] = $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
            return $ret;
        } catch (\PDOException $e) {
            return $this->db->exception($e);
        }
    }
}
