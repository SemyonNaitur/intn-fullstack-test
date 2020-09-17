<?php
require_once UTILS_DIR . '/DBUtil.php';

class Post extends DBUtil
{

    protected $fields = [
        'id' => [
            'type' => 'integer',
        ],
        'user_id' => [
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
        'updated_at' => [
            'type' => 'date',
        ],
        'created_at' => [
            'type' => 'date',
        ],
    ];

    public function __construct($db_config)
    {
        parent::__construct($db_config);
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
