<?php
require_once UTILS_DIR . '/DBUtil.php';

class Post extends DBUtil
{
    protected $table = 'posts';
    protected $fields = [
        'id' => [
            'col' => 'id',
            'type' => 'integer',
        ],
        'userId' => [
            'col' => 'user_id',
            'type' => 'integer',
            'required' => true,
        ],
        'title' => [
            'col' => 'title',
            'type' => 'string',
            'required' => true,
        ],
        'body' => [
            'col' => 'body',
            'type' => 'string',
            'required' => true,
        ],
        'updatedAt' => [
            'col' => 'updated_at',
            'type' => 'date',
        ],
        'createdAt' => [
            'col' => 'created_at',
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
        $sql = "INSERT INTO $tbl (id,user_id,title,body) VALUES (:id,:userId,:title,:body)";
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

    public function search_by_id(int $post_id)
    {
    }

    public function search_by_user_id(int $user_id)
    {
    }

    public function search_by_content(string $search_term)
    {
    }

    public function create($post)
    {
    }
}
