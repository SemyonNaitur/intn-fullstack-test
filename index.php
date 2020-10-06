<?php

define('ROOT_DIR', __DIR__);
define('SYS_DIR', ROOT_DIR . '/system');

require_once SYS_DIR . '/config.php';
require_once APP_DIR . '/config.php';

//$page = (empty($_GET['page'])) ? 'create-post' : $_GET['page'];

include TEMPLATE_DIR . '/header.php';

$router = new Router(['routes' => $routes]);
$path = $_SERVER['PATH_INFO'];
['route' => $route, 'params' => $params] = $router->match_url($path);
if ($route) {
    [$controller_path, $method] = explode('::', $route['method']);
    $controller = load_controller($controller_path);
    $controller->$method($params, $route['data']);
} else {
    http_response_code(404);
    die('<h4>Page not found</h4>');
}

include TEMPLATE_DIR . '/footer.php';


function load_controller(string $path)
{
    $controller_class = ltrim(strrchr($path, '/'), '/');
    $file = CONTROLLERS_DIR . "/$path.php";
    if (!file_exists($file)) {
        throw new Exception("Failed to load controller: $file");
    }
    require_once $file;
    return new $controller_class();
}
