<?php
require_once UTILS_DIR . '/AjaxUtil.php';

class BlogAjax extends AjaxUtil
{

	protected $post;
	protected $user;
	protected $curl;
	protected $data_url = 'https://jsonplaceholder.typicode.com';

	function __construct(array $input, Post $post, User $user, CURLUtil $curl)
	{
		parent::__construct($input);
		$this->post = $post;
		$this->user = $user;
		$this->curl = $curl;
	}

	public function fetch_posts()
	{
		$url = "$this->data_url/posts";
	}

	public function fetch_users()
	{
		$url = "$this->data_url/users";
	}
}
