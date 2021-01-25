<?php

app_config('db', [
    'default' => [
        'host' => app_getenv('DB_HOST') ?? 'localhost',
        'dbname' => app_getenv('DB_NAME') ?? 'mvc_sandbox',
        'user' => app_getenv('DB_USERNAME') ?? 'root',
        'pass' => app_getenv('DB_PASSWORD') ?? ''
    ],
]);
