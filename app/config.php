<?php
require_once 'routes.php';

define('DEBUG', true);

$app_config['app_name'] = 'blog';
$app_config['log_dir'] = ROOT_DIR . '/log';
$app_config['log_format'] = '[Y-m-d H:i:s] {level}: {message}';

$app_config['db'] = [
    'default' => [
        'host' => 'localhost',
        'dbname' => 'intn_blog',
        'user' => 'root',
        'pass' => ''
    ],
];


$app_config['routes'] = $router_test_routes; //$routes;