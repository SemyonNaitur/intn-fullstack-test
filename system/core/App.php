<?php

namespace System\Core;

class App
{

    private static App $instance;

    private Loader $loader;
    private Request $request;
    private Router $router;
    private Controller $controller;

    private function __construct()
    {
    }

    public static function bootstrap(
        Loader $loader,
        Request $request,
        Router $router
    ) {
        if (isset(self::$instance)) {
            throw new \Exception('App already created');
        }
        $instance = self::$instance = new self();
        $instance->loader = $loader;
        $instance->request = $request;
        $instance->router = $router;
        return $instance;
    }

    public static function getInstance()
    {
        return self::$instance;
    }

    public function run()
    {
        $preload = get_config('preload');

        if ($preload['db']) $this->loader->db();

        ['route' => $route, 'params' => $params] = $this->router->matchUrl($this->request->uri());

        if ($route) {
            [$controller_path, $method] = explode('::', $route['method']);

            $cls = Loader::checkSuffix('controller', $controller_path);
            $cls = get_config('controllers_path') . '/' . $cls;
            $cls = str_replace('/', '\\', $cls);

            $c = new $cls();
            $c->$method($params, $route['data']);
            $this->controller = $c;
        } else {
            http_response_code(404);
            die('<h4>Page not found</h4>');
        }
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getLoader(): Loader
    {
        return $this->loader;
    }
}
