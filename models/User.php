<?php
require_once UTILS_DIR . '/DBUtil.php';
require_once UTILS_DIR . '/Validator.php';

class User extends DBUtil
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
                $record = $this->filter_fields($record);
                $stmt->execute($record);
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
            $ebag = [];
            $pdo = $this->pdo;
            $tbl = $this->table;

            $record = $this->filter_fields($record, ['name', 'email']);
            $record['email'] = trim(($record['email'] ?? ''));

            $rules = [
                'name' => 'required|string',
                'email' => 'required|string',
            ];

            if (($valid = Validator::validate($record, $rules)) !== true) {
                $ebag = $valid;
            }

            if (!$this->is_unique($record['email'], 'email')) {
                $err = ['Email already exists'];
                $ebag['email'] = (empty($ebag['email'])) ? $err : array_merge($ebag['email'], $err);
            }

            if ($ebag) {
                $ret['error'] = 'Validation failed.';
                $ret['error_bag'] = $ebag;
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
