<?php

namespace System\Core;

use System\Libraries\Db;

class Core
{

    private Loader $loader;
    private Request $request;
    private Router $router;
    private Db $db;
    private Controller $controller;

    public function __construct(
        Loader $loader,
        Request $request,
        Router $router
    ) {
        $this->loader = $loader;
        $this->request = $request;
        $this->router = $router;
    }

    public function init()
    {
        $preload = get_config('preload');

        $this->db = ($preload['db']) ? $this->loader->db() : null;

        ['route' => $route, 'params' => $params] = $this->router->matchUrl($this->request->uri());

        if ($route) {
            [$controller_path, $method] = explode('::', $route['method']);
            $c = $this->loadController($controller_path);
            $c->init($this->request, $this->loader, $this->db);
            $c->$method($params, $route['data']);
            $this->controller = $c;
        } else {
            http_response_code(404);
            die('<h4>Page not found</h4>');
        }
    }

    protected function loadController(string $path): Controller
    {
        $cls = get_config('controllers_path') . '/' . trim($path, '/');
        $cls = str_replace('/', '\\', $cls);
        return new $cls();
    }
}
