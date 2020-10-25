<?php

use System\Controller;

class Test extends Controller
{

    public function __construct()
    {
    }

    public function print_request(?array $params, ?array $data)
    {
        echo '<pre>';
        print_r($_SERVER);
        echo '</pre>';
    }

    public function cat_id_prop(?array $params, ?array $data)
    {
        echo "Displaying property <b>$params[prop]</b> of item <b>#$params[id]</b> from category <b>$params[cat]</b>.";
    }

    public function cat_id_props(?array $params, ?array $data)
    {
        if (count($params['rest_params']) == 1) {
            $to = preg_replace('/\/(cat-id-prop)s\//', '/$1/', $this->request->url());
            $this->request->redirect($to);
        }
        echo "Displaying properties <b>" . implode(',', $params['rest_params']) . "</b> of item #$params[id] from category '$params[cat]'.";
    }

    public function regex_route(?array $params, ?array $data)
    {
        echo "Regex route works.";
    }

    public function callback_route(?array $params, ?array $data)
    {
        echo "Callback route " . (($params['works']) ? 'works' : '');
    }

    public function not_found(?array $params, ?array $data)
    {
        http_response_code(404);
        die('Page not found.');
    }
}
