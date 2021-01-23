<?php
require 'databases.php';
require 'routes.php';

app_config('app_name', 'MVC Sandbox');

app_config('log_dir', ROOT_DIR . '/log');
app_config('log_format', '[Y-m-d H:i:s] {level}: {message}');

if (getenv('APP_ENVIRONMENT') === 'production') {
    app_config('debug', (bool) getenv('APP_DEBUG'));
} else {
    app_config('debug', true);
}

app_config('preload', [
    'db' => true,
]);
