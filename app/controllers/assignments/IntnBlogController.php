<?php

namespace App\Controllers\Assignments;

use System\Core\{Controller, Loader};

class IntnBlogController extends Controller
{

    protected $views_folder = 'assignments/intn-blog';

    public function createPost(?array $params, ?array $data)
    {
        $folder = "$this->views_folder/create-post";
        $opts = [Loader::RETURN];
        $components = [
            'create_post_form' => $this->load->view("$folder/components/create-post-form", null, $opts),
        ];
        $content = [
            'top_nav' => $this->load->view("$this->views_folder/top-nav", null, $opts),
            'page_content' => $this->load->view("$folder/page-layout", $components, $opts),
        ];
        $this->load->styles('test');
        $this->load->scripts('test', [Loader::INLINE]);
        $this->render($content);
    }

    public function posts(?array $params, ?array $data)
    {
        $folder = "$this->views_folder/posts";
        $opts = [Loader::RETURN];
        $content = [
            'top_nav' => $this->load->view("$this->views_folder/top-nav", null, $opts),
            'page_content' => $this->load->view("$folder/page-layout", null, $opts),
        ];
        $this->render($content);
    }

    public function stats(?array $params, ?array $data)
    {
        $folder = "$this->views_folder/stats";
        $opts = [Loader::RETURN];
        $components = [
            'user_stats' => $this->load->view("$folder/components/user-stats", null, $opts),
        ];
        $content = [
            'top_nav' => $this->load->view("$this->views_folder/top-nav", null, $opts),
            'page_content' => $this->load->view("$folder/page-layout", $components, $opts),
        ];
        $this->render($content);
    }
}
