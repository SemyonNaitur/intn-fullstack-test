<?php
require_once UTILS_DIR . '/DBUtil.php';

class User extends DBUtil
{

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

    public function create($user)
    {
    }
}
