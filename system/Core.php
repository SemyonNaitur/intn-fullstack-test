<?php

namespace System;

class Core
{

    private Request $request;
    private Router $router;
    private Loader $load;

    private DB $db;
    private Controller $controller;

    public function __construct(array $config = null)
    {
        $this->request = new Request();
        $this->router = new Router(['routes' => $config['routes']]);
        $this->load = new Loader();
        if (isset($config['db'])) {
            $this->db = $this->load->db($config['db']);
        }
    }

    public function init()
    {
        ['route' => $route, 'params' => $params] = $this->router->matchUrl($this->request->uri());

        if ($route) {
            [$controller_path, $method] = explode('::', $route['method']);
            $c = $this->load->controller($controller_path);
            $c->init($this->request, $this->db, $this->load);
            $c->$method($params, $route['data']);
            $this->controller = $c;
        } else {
            http_response_code(404);
            die('<h4>Page not found</h4>');
        }
    }
}
