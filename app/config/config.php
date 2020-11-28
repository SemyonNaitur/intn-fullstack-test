<?php
require 'databases.php';
require 'routes.php';

app_config('app_name', 'MVC Sandbox');

app_config('log_dir', ROOT_DIR . '/log');
app_config('log_format', '[Y-m-d H:i:s] {level}: {message}');

app_config('debug', true);

app_config('preload', [
    'db' => true,
]);
