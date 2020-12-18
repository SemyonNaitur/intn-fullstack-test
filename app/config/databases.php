<?php

app_config('db', [
    'default' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'dbname' => 'mvc_sandbox',
        'user' => getenv('DB_USERNAME') ?: 'root',
        'pass' => getenv('DB_PASSWORD') ?: ''
    ],
]);
