<?php

class Router
{
    protected $routes;
    protected $route_404;

    public function __construct(array $config)
    {
        $routes = $config['routes'] ?? null;
        if (!is_Array($routes) || count($routes) < 1) {
            throw new Error('Invalid routes array');
        }
        $this->init_routes($routes);
    }

    /**
     * route config format:
     *      match condition - one of the 3 must be supplied:
     *          path: <segment>/<:param>/.../<rest params (...)>, e.g., 'category/:id/...'
     *          regex: overrides path
     *          callback: fn (url) => array | bool, overrides path and regex
     * 
     *      method:
     *          <path>/<controller>::<method>
     *          e.g., api/blog::create_post
     */
    protected function init_routes(array $routes_config)
    {
        $routes = [];
        foreach ($routes_config as $i => $cfg) {
            $route = [];
            if (!is_array($cfg)) {
                throw new Exception("Invalid route config array at index $i");
            }

            $method = $cfg['method'] ?? null;
            if (!is_string($method) || count(explode('::', $method)) != 2) {
                throw new Exception("Missing or invalid \"method\" for route config at index $i");
            }
            $route['method'] = $method;

            $cb = ($cfg['callback'] ?? null) ?: null;
            $regex = $cfg['regex'] ?? null;
            $path = $cfg['path'] ?? null;
            if ($cb) {
                if (!is_callable($cb)) {
                    throw new Exception("Ivalid callback for route at index $i");
                }
                $route['callback'] = $cb;
            } elseif ($regex) {
                if ($err = $this->invalid_regex($regex)) {
                    throw new Exception("Ivalid regex pattern for route at index $i:\n$err");
                }
                $route['regex'] = $regex;
            } else {
                if (!is_string($path)) {
                    throw new Exception("Invalid route path at index $i");
                }
                $route['path'] = $path;
                $route['regex'] = $this->parse_path($path);
                if (trim($path, '/') === 'not-found') {
                    $this->route_404 = $route;
                }
            }
            $routes[] = $route;
        }
        $this->routes = $routes;
    }

    protected function invalid_regex($patt)
    {
        try {
            preg_match($patt, null);
            return false;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    /**
     * Transforms the path string to a regex by replacing each named url segment
     * with a corresponding named capturing group - e.g., :id -> (?<id>[^/]+)
     * A trailing '/...' notation is transformed to '_restString' group.
     * 
     * Leading/trailing '/'s are removed.
     */
    protected function parse_path(string $path)
    {
        $patt = trim($path, '/');
        $patt = preg_replace('/:([^\/]+)/', '(?<param_$1>[^/]+)', $patt); // parse params
        $patt = preg_replace('/\.{3}$/', '(?<param__rest_string>.+)?', $patt); // parse rest string
        $patt = preg_quote($patt, '/');
        return "/^$patt$/";
    }

    protected function parse_url($url)
    {
        $url = explode('?', $url);
        $path = trim(preg_replace('/\/{2,}/', '/', $url[0]), '/'); // remove excess '/'
        $query_string = $url[1] ?? null;
        $query_params = [];
        if ($query_string) {
            foreach (explode('&', $url[1]) as $param) {
                $param = explode('=', $param);
                $query_params[$param[0]] = $param[1] ?? '';
            }
        }
        return compact('path', 'query_string', 'query_params');
    }

    /**
     * @param   string      $url - <path>[?<queryString>] - without domain.
     * @return  array|null
     */
    public function match_url($url)
    {
        $ret = $match = $params = $route = null;
        $url_data = $this->parse_url($url);
        foreach ($this->routes as $route) {
            if (isset($route['regex'])) {
                if (preg_match($route['regex'], $url_data['path'], $match)) {
                    foreach ($match as $k => $v) {
                        if (is_numeric($k)) continue;
                        $k = str_replace('param_', '', $k);
                        $params[$k] = $v;
                    }
                    break;
                }
            } else {
                if ($match = $route['callback']($url_data['path'])) {
                    $params = (is_array($match)) ? $match : null;
                    break;
                }
            }
            $match = null;
        }

        if (!$match) {
            $route = $this->route_404;
        }

        if ($route) {
            if (isset($params['_rest_string'])) {
                $params['rest_params'] = explode('/', $params['_rest_string']);
            }
            $params['query_params'] = $url_data['query_params'];
            $ret = compact('route', 'params');
        }

        return $ret;
    }
}
