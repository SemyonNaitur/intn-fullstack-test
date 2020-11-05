<?php

namespace System\Core;

use System\Libraries\Db;

class Core
{

    private Request $request;
    private Router $router;
    private Loader $loader;
    private Db $db;
    private Controller $controller;

    public function __construct(array $config = null)
    {
        $this->request = new Request();
        $this->router = new Router(['routes' => $config['routes']]);
        $this->loader = new Loader();

        $preload = get_config('preload');

        $this->db = ($preload['db']) ? $this->loader->db() : null;
    }

    public function init()
    {
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

    /**
     * Loads the requested controller class, creates an instance and returns it.
     * 
     * @param   string  $path
     * @return  Controller
     */
    public function loadController(string $path): Controller
    {
        $controller_class = ltrim(strrchr($path, '/'), '/');
        $file = sprintf('%s/%s.php', CONTROLLERS_DIR, trim($path, '/'));
        if (!file_exists($file)) {
            throw new \Exception("Failed to load controller: $file");
        }
        include_once $file;
        return new $controller_class();
    }
}
