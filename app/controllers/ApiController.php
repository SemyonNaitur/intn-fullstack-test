<?php

namespace App\Controllers;

use System\Core\Controller;

class ApiController extends Controller
{

    public function index(?array $params, ?array $data)
    {
        try {
            switch ($params['name']) {
                case 'intn-blog':
                    return $this->intnBlog($params, $data);
            }
        } catch (\Throwable $e) {
            if (app_config('debug')) {
                throw $e;
            } else {
                http_response_code(500);
            }
        }
    }

    public function intnBlog(?array $params, ?array $data)
    {
        $args = [
            $this->request->input(),
            $this->db,
            $this->load->library('Curl'),
            $this->load->library('Validator', [$this->db]),
            $this->load->model('User'),
            $this->load->model('Post'),
        ];
        $api = $this->load->library('api/IntnBlog', $args);
        $api->run();
    }
}
