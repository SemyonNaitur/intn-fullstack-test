<?php

define('DEBUG', true);

//---  DB ---//
define('DB_CONFIG', [
    'host' => 'localhost',
    'dbname' => 'intn_blog',
    'user' => 'root',
    'pass' => ''
]);
//---  /DB ---//

function route_callback_test($url)
{
    return ($url === 'callback-route') ? ['works' => true] : false;
}

$routes = [
    ['path' => '/print-request', 'method' => 'test/test::print_request'],
    ['path' => 'cat-id-prop/:cat/:id/:prop', 'method' => 'test/test::cat_id_prop'],
    ['path' => 'cat-id-prop/:cat/:id/...', 'method' => 'test/test::cat_id_prop'],
    // ['path' => 'cat-id-props/:cat/:id/...', 'method' => 'test/test::cat_id_props'],
    // ['regex' => '/regex\/route/', 'method' => 'test/test::regex_route'],
    ['callback' => 'route_callback_test', 'method' => 'test/test::callback_route'],
    // ['path' => 'not-found', 'method' => 'test/test::callback_route'],
];