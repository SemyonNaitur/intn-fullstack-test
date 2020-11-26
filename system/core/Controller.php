<?php

namespace System\Core;

use System\Libraries\Db;

abstract class Controller
{

    private static Controller $instance;

    protected Request $request;
    protected Loader $load;
    protected Db $db;

    private function __construct()
    {
    }

    public static function bootstrap(
        Request $request,
        Loader $loader,
        ?Db $db
    ): Controller {
        $instance = new static();
        $instance->request = $request;
        $instance->load = $loader;
        $instance->db = $db;
        return $instance;
    }

    protected function render(array $content, bool $return = false, string $template = null)
    {
        $template = $template ?? 'main';
        $opts = [Loader::STYLES, Loader::SCRIPTS];
        if ($return) $opts[] = Loader::RETURN;
        $this->load->view($template, $content, $opts);
    }
}
