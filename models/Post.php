<?php
require_once UTILS_DIR . '/DBUtil.php';


class Post extends DBUtil
{
    protected $table = 'posts';
    protected $primary_key = 'id';
    protected $fields = [
        'id' => [
            'type' => 'integer',
        ],
        'userId' => [
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

            $sql = "INSERT INTO $tbl (id,user_id,title,body) VALUES (:id,:userId,:title,:body)";
            $stmt = $pdo->prepare($sql);
            $pdo->beginTransaction();
            foreach ($data as $row) {
                $row = $this->filter_fields($row);
                $stmt->execute($row);
            }
            $pdo->commit();
            $ret['inserted'] = count($data);
            return $ret;
        } catch (PDOException $e) {
            $pdo->rollback();
            return $this->db_exception($e);
        }
    }

    public function create(array $record)
    {
        try {
            $ret = ['record' => null, 'error' => ''];
            $pdo = $this->pdo;
            $tbl = $this->table;
            $record = $this->filter_fields($record, ['userId', 'title', 'body']);

            $sql = "INSERT INTO $tbl (user_id,title,body) VALUES (:userId,:title,:body)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($record);
            $record['id'] = $pdo->lastInsertId();
            $ret['record'] = $record;
            return $ret;
        } catch (PDOException $e) {
            return $this->db_exception($e);
        }
    }

    public function search_by_id($id)
    {
        try {
            $ret = ['row' => null, 'error' => ''];
            $pdo = $this->pdo;
            $tbl = $this->table;
            $pk = $this->primary_key;

            $sql = "SELECT * FROM $tbl WHERE $pk=? LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $ret['row'] = $stmt->fetch(PDO::FETCH_ASSOC);
            return $ret;
        } catch (PDOException $e) {
            return $this->db_exception($e);
        }
    }

    public function search_by_user_id($user_id)
    {
        try {
            $ret = ['result' => null, 'error' => ''];
            $pdo = $this->pdo;
            $tbl = $this->table;

            $sql = "SELECT * FROM $tbl WHERE user_id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            $ret['result'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $ret;
        } catch (PDOException $e) {
            return $this->db_exception($e);
        }
    }

    public function search_by_content($search)
    {
        try {
            $ret = ['result' => null, 'error' => ''];
            $pdo = $this->pdo;
            $tbl = $this->table;

            $sql = "SELECT * FROM $tbl WHERE title LIKE :search OR body LIKE :search";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['search' => "%$search%"]);
            $ret['result'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $ret;
        } catch (PDOException $e) {
            return $this->db_exception($e);
        }
    }

    public function user_stats()
    {
        try {
            $ret = ['result' => null, 'error' => ''];
            $pdo = $this->pdo;
            $tbl = $this->table;

            $sql  = "SELECT user_id, ROUND((COUNT(*) / 12), 2) AS monthly_average, ROUND((COUNT(*) / (365/7)), 2) AS weekly_average ";
            $sql .= "FROM $tbl GROUP BY user_id, YEAR(created_at) ORDER BY user_id";
            $ret['result'] = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            return $ret;
        } catch (PDOException $e) {
            return $this->db_exception($e);
        }
    }
}
