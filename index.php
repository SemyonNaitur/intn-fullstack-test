<?php
require_once 'config.php';
require_once UTILS_DIR . '/helper.php';
require_once UTILS_DIR . '/Router.php';

//$page = (empty($_GET['page'])) ? 'create-post' : $_GET['page'];

include TEMPLATE_DIR . '/header.php';

$router = new Router(['routes' => $routes]);
$path = $_SERVER['PATH_INFO'];
['route' => $route, 'params' => $params] = $router->match_url($path);
if ($route) {
    [$controller_path, $method] = explode('::', $route['method']);
    $controller = load_controller($controller_path);
    $controller->$method($params);
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
