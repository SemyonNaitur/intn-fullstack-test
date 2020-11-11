<?php

namespace App\Libraries\Api;

use System\Core\Loader;
use System\Libraries\{Validator, Curl};
use System\Libraries\Api\ApiBase;

use App\Models\{User, Post};

class IntnBlog extends ApiBase
{
	protected Loader $load;

	protected User $user;
	protected Post $post;

	protected $data_url = 'https://jsonplaceholder.typicode.com';

	function __construct(array $input, Loader $loader)
	{
		parent::__construct($input);
		$this->load = $loader;
		$this->user = $this->load->model('User');
		$this->post = $this->load->model('Post');
	}

	//--- API methods ---//

	public function fetchRemoteData(array $params)
	{
		$data = [
			'inserted_users' => 0,
			'inserted_posts' => 0,
		];
		$err = null;

		$url = $this->data_url;
		$res = $this->fetchUsers($url);
		if (!($err = $res['error'])) {
			$data['inserted_users'] = $res['inserted'];
		}

		if ($data['inserted_users']) {
			$res = $this->fetchPosts($url);
			if (!($err = $res['error'])) {
				$data['inserted_posts'] = $res['inserted'];
			}
		}

		($err) ? $this->error($err) : $this->success($data);
	}

	public function createUser(array $params)
	{
		$data = $err = null;
		if (!($missing = $this->checkMissing($params, ['user']))) {
			if ($this->validateUser($params['user'])) {
				$res = $this->user->create($params['user']);
				if (!($err = $res['error'])) {
					$data = $res['record'];
				}
			}
		}
		if ($missing) $this->paramsError($missing);
		if ($err) $this->error($err);
		if ($error_bag = $this->validator->get_error_bag()) $this->validationFail($error_bag);
		$this->success($data);
	}

	public function createPost(array $params)
	{
		try {
			$db = $this->load->db();
			$transaction = false;
			$err = null;
			$error_bag = null;

			$user = $params['user'];
			$user['id'] = (($user['id'] ?? 0) > 0) ? $user['id'] : null;
			$user['email'] = trim(($user['email'] ?? ''));

			$post = $params['post'];
			$post['id'] = null;

			if (!$user['id']) {
				$this->validateUser($user);
			}
			$this->validatePost($post);

			if (!($error_bag = $this->validator->getErrorBag())) {
				if (!$user['id']) {
					$db->beginTransaction();
					$transaction = true;
					$res = $this->user->create($user);
					if (!($err = $res['error'])) {
						$user = $res['record'];
					}
				}

				if ($post['userId'] = $user['id']) {
					$res = $this->post->create($post);
					if (!($err = $res['error'])) {
						$post = $res['record'];
					}
				}
			}

			if ($error_bag) {
				$this->validationFail($error_bag);
			} elseif (!$post['id']) {
				if ($transaction) $db->rollback();
				$this->error($err);
			} else {
				if ($transaction) $db->commit();
				$this->success(compact('user', 'post'));
			}
		} catch (\PDOException $e) {
			$db->exception($e);
		}
	}

	public function userStats(array $params)
	{
		$res = $this->post->userStats();
		if ($res['error']) {
			$this->error($res['error']);
		} else {
			$this->success($res['result']);
		}
	}
	//--- /API methods ---//


	protected function fetchUsers($url)
	{
		$ret = ['inserted' => 0, 'error' => ''];
		$curl_res = $this->curl->getContent("$url/users");
		if ($curl_res['error']) {
			$ret['error'] = $curl_res['error'];
		} else {
			$data = json_decode($curl_res['result'], true);
			$db_res = $this->user->insertBatch($data);
			if ($db_res['error']) {
				$ret['error'] = $db_res['error'];
			} else {
				$ret['inserted'] = $db_res['inserted'];
			}
		}
		return $ret;
	}

	protected function fetchPosts($url)
	{
		$ret = ['inserted' => 0, 'error' => ''];
		$curl_res = $this->curl->getContent("$url/posts");
		if ($curl_res['error']) {
			$ret['error'] = $curl_res['error'];
		} else {
			$data = json_decode($curl_res['result'], true);
			$db_res = $this->post->insertBatch($data);
			if ($db_res['error']) {
				$ret['error'] = $db_res['error'];
			} else {
				$ret['inserted'] = $db_res['inserted'];
			}
		}
		return $ret;
	}

	protected function validateUser(array $user)
	{
		$rules = [
			'name:Name' => 'required|string|min_length:2',
			'email:Email' => 'required|unique:users.email',
		];
		return $this->validator->validate($user, $rules);
	}

	protected function validatePost(array $post)
	{
		$rules = [
			'title:Title' => 'required|min_length:2',
			'body:Body' => 'required|min_length:2',
		];
		return $this->validator->validate($post, $rules);
	}
}
