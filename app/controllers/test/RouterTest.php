<?php

//namespace App\Controllers\Test;

use System\Controller;

class RouterTest extends Controller
{

    public function __construct()
    {
    }

    public function printRequest(?array $params, ?array $data)
    {
        echo '<pre>';
        if ($data['print_args']) {
            print_r($params);
            print_r($data);
        }
        print_r($_GET);
        print_r($_SERVER);
        echo '</pre>';
    }

    public function catIdProp(?array $params, ?array $data)
    {
        echo "Displaying property <b>$params[prop]</b> of item <b>#$params[id]</b> from category <b>$params[cat]</b>.";
    }

    public function catIdProps(?array $params, ?array $data)
    {
        if (count($params['rest_params']) == 1) {
            $to = preg_replace('/\/(cat-id-prop)s\//', '/$1/', $this->request->url());
            $this->request->redirect($to);
        }
        echo "Displaying properties <b>" . implode(',', $params['rest_params']) . "</b> of item #$params[id] from category '$params[cat]'.";
    }

    public function regexRoute(?array $params, ?array $data)
    {
        echo "Regex route works.";
    }

    public function callbackRoute(?array $params, ?array $data)
    {
        echo "Callback route " . (($params['works']) ? 'works' : '');
    }

    public function notFound(?array $params, ?array $data)
    {
        http_response_code(404);
        die('Page not found.');
    }
}
