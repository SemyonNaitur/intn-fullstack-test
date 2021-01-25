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
                case 'nbm-crawler':
                    return $this->nbmCrawler($params, $data);
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
        $folder = 'IntnBlog';
        $args = [
            $this->request->input(),
            $this->db,
            $this->load->library('Curl'),
            $this->load->library('Validator'),
            $this->load->model("$folder/User"),
            $this->load->model("$folder/Post"),
        ];
        $api = $this->load->library('api/IntnBlog', $args);
        $api->run();
    }

    public function nbmCrawler(?array $params, ?array $data)
    {
        $folder = 'NbmCrawler';
        $args = [
            $this->request->input(),
            $this->db,
            $this->load->library('Curl'),
            $this->load->library('Validator'),
            $this->load->model("$folder/Link"),
        ];
        $api = $this->load->library('api/NbmCrawler', $args);
        $api->run();
    }
}
