<?php

$rtc = 'test/RouterTest';
$ibp = 'assignments/intn-blog';
$ibc = 'assignments/IntnBlog';

$app_config['routes'] = [
    /**
     * Intn blog.
     */
    ['path' => "$ibp/create-post", 'method' => "$ibc::createPost"],
    ['path' => "$ibp/posts", 'method' => "$ibc::posts"],
    ['path' => "$ibp/stats", 'method' => "$ibc::stats"],

    /**
     * Router test.
     */
    ['path' => '/print-request', 'method' => "$rtc::printRequest", 'data' => ['print_args' => true]],
    ['path' => 'cat-id-prop/:cat/:id/:prop', 'method' => "$rtc::catIdProp"],
    ['path' => 'cat-id-prop/:cat/:id/...', 'method' => "$rtc::catIdProps"],
    ['path' => 'cat-id-props/:cat/:id/...', 'method' => "$rtc::catIdProps"],
    ['regex' => '/regex\/route/', 'method' => "$rtc::regexRoute"],
    ['callback' => 'route_callback_test', 'method' => "$rtc::callbackRoute"],
    ['path' => 'not-found', 'method' => "$rtc::notFound"],
];
unset($rtc, $ibp, $ibc);


function route_callback_test($url)
{
    return ($url === 'callback-route') ? ['works' => true] : false;
}
