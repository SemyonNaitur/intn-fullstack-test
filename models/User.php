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
        $stmt = $pdo->prepare($sql);
        try {
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
    }
}
