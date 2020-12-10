<?php

use System\Core\{App, Loader, Request, Router};

define('ROOT_DIR', __DIR__);
define('SYS_DIR', ROOT_DIR . '/system');

require_once SYS_DIR . '/config.php';

(App::bootstrap(
    new Loader(),
    new Request(),
    new Router(['routes' => app_config('routes')])
))->run();

function base_url(): string
{
    return App::request()::base();
}

function log_debug(string $message): void
{
    App::logger()->debug($message);
}

function log_error(string $message): void
{
    App::logger()->error($message);
}

function html_title(string $title = null)
{
    static $html_title = '';

    if (is_string($title)) {
        $html_title = strip_tags($title);
    } else {
        return $html_title ?: app_config('app_name');
    }
}
