<?php

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


$app_config['routes'] = [
    ['path' => '/print-request', 'method' => 'test/test::print_request'],
    ['path' => 'cat-id-prop/:cat/:id/:prop', 'method' => 'test/test::cat_id_prop'],
    ['path' => 'cat-id-prop/:cat/:id/...', 'method' => 'test/test::cat_id_props'],
    ['path' => 'cat-id-props/:cat/:id/...', 'method' => 'test/test::cat_id_props'],
    ['regex' => '/regex\/route/', 'method' => 'test/test::regex_route'],
    ['callback' => 'route_callback_test', 'method' => 'test/test::callback_route'],
    ['path' => 'not-found', 'method' => 'test/test::not_found'],
];


function route_callback_test($url)
{
    return ($url === 'callback-route') ? ['works' => true] : false;
}
