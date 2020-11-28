<?php

namespace App\Controllers\Assignments;

use System\Core\{Controller, Loader};

class IntnBlogController extends Controller
{

    protected $folder = 'assignments/intn-blog';

    public function __construct()
    {
        parent::__construct();
        $this->load->scripts("$this->folder/main");
    }

    public function createPost(?array $params, ?array $data)
    {
        $folder = "$this->folder/create-post";
        $opts = [Loader::RETURN];
        $components = [
            'create_post_form' => $this->load->view("$folder/components/create-post-form", null, $opts),
        ];
        $content = [
            'top_nav' => $this->load->view("$this->folder/top-nav", null, $opts),
            'page_content' => $this->load->view("$folder/page-layout", $components, $opts),
        ];
        $this->load->styles('test');
        $this->render($content);
    }

    public function posts(?array $params, ?array $data)
    {
        $folder = "$this->folder/posts";
        $opts = [Loader::RETURN];
        $content = [
            'top_nav' => $this->load->view("$this->folder/top-nav", null, $opts),
            'page_content' => $this->load->view("$folder/page-layout", null, $opts),
        ];
        $this->render($content);
    }

    public function stats(?array $params, ?array $data)
    {
        $folder = "$this->folder/stats";
        $opts = [Loader::RETURN];
        $components = [
            'user_stats' => $this->load->view("$folder/components/user-stats", null, $opts),
        ];
        $content = [
            'top_nav' => $this->load->view("$this->folder/top-nav", null, $opts),
            'page_content' => $this->load->view("$folder/page-layout", $components, $opts),
        ];
        $this->render($content);
    }

    public function postsJson(?array $params, ?array $data)
    {
        try {
            $input = $this->request->input();
            $by = array_keys($input)[0];
            $param = $input[$by];

            $by = str_replace('_', ' ', $by);
            $by = str_replace(' ', '', ucfirst($by));

            $post = $this->load->model('Post');
            $res = $post->{"searchBy$by"}($param);
            if ($res['error']) {
                $data = $res;
            } else {
                $data = $res['row'] ?? $res['result'];
            }

            header('Content-type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
            die;
        } catch (\Throwable $e) {
            if (get_config('debug')) {
                throw $e;
            } else {
                http_response_code(500);
            }
        }
    }
}
