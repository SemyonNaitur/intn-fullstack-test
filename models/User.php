<?php
require_once UTILS_DIR . '/DBUtil.php';

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
        $ret = ['inserted' => 0, 'error' => ''];
        $pdo = $this->pdo;
        $tbl = $this->table;
        $sql = "INSERT INTO $tbl (id,name,email) VALUES (:id,:name,:email)";
        try {
            $stmt = $pdo->prepare($sql);
            $pdo->beginTransaction();
            foreach ($data as $row) {
                $row = $this->filter_fields($row);
                $stmt->execute($row);
            }
            $pdo->commit();
            $ret['inserted'] = count($data);
        } catch (Throwable $e) {
            $pdo->rollback();
            return $this->exception($e);
        }
        return $ret;
    }

    public function create($user)
    {
        $ret = ['user' => null, 'error' => ''];
        $pdo = $this->pdo;
        $tbl = $this->table;

        $user = $this->filter_fields($user, ['name', 'email']);
        $rules = [
            'name' => 'required|string',
            'email' => 'required|string',
        ];
        if (($valid = $this->validate($user, $rules)) !== true) {
            $ret['error'] = 'Validation failed.';
            $ret['error_bag'] = $valid;
        } else {
            $sql = "INSERT INTO $tbl (name,email) VALUES (:name,:email)";
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($user);
                $user['id'] = $pdo->lastInsertId();
                $ret['user'] = $user;
            } catch (Throwable $e) {
                return $this->exception($e);
            }
        }
        return $ret;
    }
}
