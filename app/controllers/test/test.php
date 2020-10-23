<?php

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
        echo "Displaying properties <b>" . implode(',', $params['props']) . "</b> of item #$params[id] from category '$params[cat]'.";
    }
}
