<?php
require_once UTILS_DIR . '/DBUtil.php';
require_once UTILS_DIR . '/Validator.php';

class User extends DBUtil
{
    protected $table = 'users';
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
            'type' => 'date',
        ],
        'createdAt' => [
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

            $record = $this->filter_fields($record, ['name', 'email']);
            $rules = [
                'name' => 'required|string',
                'email' => 'required|string',
            ];
            if (($valid = Validator::validate($record, $rules)) !== true) {
                $ret['error'] = 'Validation failed.';
                $ret['error_bag'] = $valid;
            } else {
                $sql = "INSERT INTO $tbl (name,email) VALUES (:name,:email)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($record);
                $record['id'] = $pdo->lastInsertId();
                $ret['record'] = $record;
            }
            return $ret;
        } catch (PDOException $e) {
            return $this->db_exception($e);
        }
    }
}
