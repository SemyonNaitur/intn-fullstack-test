<?php

class Core
{

    private $dbs = [];
    private Request $request;
    private Router $router;
    private DB $db;
    private Controller $controller;

    public function __construct(array $config = null)
    {
        $this->request = new Request();
        $this->router = new Router(['routes' => $config['routes']]);

        if (isset($config['db'])) {
            $this->db = $this->load_db($config['db']);
        }
    }

    public function init()
    {
        ['route' => $route, 'params' => $params] = $this->router->match_url($this->request->path());

        if ($route) {
            [$controller_path, $method] = explode('::', $route['method']);
            $this->load_controller($controller_path);
            $this->controller->$method($params, $route['data']);
        } else {
            http_response_code(404);
            die('<h4>Page not found</h4>');
        }
    }

    public function load_db(string $name)
    {
        if (isset($this->dbs[$name])) {
            return $this->dbs[$name];
        }
        $cfg = get_config('db')[$name] ?? null;
        if (!$cfg) throw new Exception("No configuration for database: $name");
        $db = $this->dbs[$name] = new DB($cfg);
        return $db;
    }

    private function load_controller(string $path)
    {
        $controller_class = ltrim(strrchr($path, '/'), '/');
        $file = CONTROLLERS_DIR . "/$path.php";
        if (!file_exists($file)) {
            throw new Exception("Failed to load controller: $file");
        }
        require_once $file;
        $c = new $controller_class();
        $c->init($this->request, $this->db);
        $this->controller = $c;
    }
}
