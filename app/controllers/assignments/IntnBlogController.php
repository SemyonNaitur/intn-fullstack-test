<?php

namespace App\Controllers\Assignments;

use System\Core\{Controller, Loader};

class IntnBlogController extends Controller
{

    protected $views_folder = 'assignments/intn-blog';

    public function createPost(?array $params, ?array $data)
    {
        $folder = "$this->views_folder/create-post";
        $content = [
            'create_post_form' => $this->load->view("$folder/components/create-post-form", null, [Loader::RETURN_VIEW]),
        ];
        $data = [
            'top_nav' => $this->load->view("$this->views_folder/template/top-nav", null, [Loader::RETURN_VIEW]),
            'page_content' => $this->load->view("$folder/page-layout", $content, [Loader::RETURN_VIEW]),
        ];
        $this->load->view("$this->views_folder/layout", $data);
    }
}
