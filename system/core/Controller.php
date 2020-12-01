<?php

namespace System\Core;

use System\Libraries\Db;

abstract class Controller
{
    protected Request $request;
    protected Loader $load;
    protected Db $db;

    public function __construct()
    {
        $app = App::getInstance();
        $this->request = $app->getRequest();
        $loader = $this->load = $app->getLoader();
        $this->db = $loader->getDefaultDb();
    }

    protected function render(array $content, bool $return = false, string $template = null)
    {
        $template = $template ?? 'main';
        $opts = [Loader::STYLES, Loader::SCRIPTS];
        if ($return) $opts[] = Loader::RETURN;
        $this->load->view($template, $content, $opts);
    }
}
