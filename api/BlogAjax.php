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

	//--- API methods ---//

	public function fetch_remote_data($params, AjaxResponse $resp)
	{
		$url = $this->data_url;

		$resp->data = [
			'inserted_users' => 0,
			'inserted_posts' => 0,
		];

		$res = $this->fetch_users($url);
		if (!empty($res['error'])) {
			$resp->status = 'FAIL';
			$resp->message = $res['error'];
		} else {
			$resp->data['inserted_users'] = $res['inserted'];
		}

		if ($resp->data['inserted_users']) {
			$res = $this->fetch_posts($url);
			if (!empty($res['error'])) {
				$resp->status = 'FAIL';
				$resp->message = $res['error'];
			} else {
				$resp->data['inserted_posts'] = $res['inserted'];
			}
		}
	}

	public function create_user($params, AjaxResponse $resp)
	{
		$res = $this->user->create($params['user']);
		if (!empty($res['error'])) {
			$resp->status = 'FAIL';
			$resp->message = $res['error'];
		} else {
			$resp->data['user'] = $res['user'];
		}
	}
	//--- /API methods ---//


	protected function fetch_users($url)
	{
		$ret = ['inserted' => 0, 'error' => ''];
		$curl_res = $this->curl->get_content("$url/users");
		if (!empty($curl_res['error'])) {
			$ret['error'] = $curl_res['error'];
		} else {
			$data = json_decode($curl_res['result'], true);
			$db_res = $this->user->insert_batch($data);
			if (!empty($db_res['error'])) {
				$ret['error'] = $db_res['error'];
			} else {
				$ret['inserted'] = $db_res['inserted'];
			}
		}
		return $ret;
	}

	protected function fetch_posts($url)
	{
		$ret = ['inserted' => 0, 'error' => ''];
		$curl_res = $this->curl->get_content("$url/posts");
		if (!empty($curl_res['error'])) {
			$ret['error'] = $curl_res['error'];
		} else {
			$data = json_decode($curl_res['result'], true);
			$db_res = $this->post->insert_batch($data);
			if (!empty($db_res['error'])) {
				$ret['error'] = $db_res['error'];
			} else {
				$ret['inserted'] = $db_res['inserted'];
			}
		}
		return $ret;
	}
}
