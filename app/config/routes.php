<?php

$app_config['routes'] = [
    /**
     * Router test routes.
     */
    ['path' => '/print-request', 'method' => 'test/RouterTest::printRequest', 'data' => ['print_args' => true]],
    ['path' => 'cat-id-prop/:cat/:id/:prop', 'method' => 'test/RouterTest::catIdProp'],
    ['path' => 'cat-id-prop/:cat/:id/...', 'method' => 'test/RouterTest::catIdProps'],
    ['path' => 'cat-id-props/:cat/:id/...', 'method' => 'test/RouterTest::catIdProps'],
    ['regex' => '/regex\/route/', 'method' => 'test/RouterTest::regexRoute'],
    ['callback' => 'route_callback_test', 'method' => 'test/RouterTest::callbackRoute'],
    ['path' => 'not-found', 'method' => 'test/RouterTest::notFound'],
];


function route_callback_test($url)
{
    return ($url === 'callback-route') ? ['works' => true] : false;
}
