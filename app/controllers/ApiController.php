<?php

namespace App\Controllers;

use System\Core\Controller;

class ApiController extends Controller
{

    public function __construct()
    {
    }

    public function intnBlog(?array $params, ?array $data)
    {
        $args = [
            $this->request->input(),
            $this->db,
            $this->load->library('Curl'),
            $this->load->library('Validator', [$this->db])
        ];
        $api = $this->load->library('BlogApi', $args);
        $api->run();
    }
}
