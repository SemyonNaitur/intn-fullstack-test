<?php
require_once UTILS_DIR . '/ApiBase.php';

require_once MODELS_DIR . '/Post.php';
require_once MODELS_DIR . '/User.php';

class BlogApi extends ApiBase
{
	protected DBUtil $db;
	protected CURLUtil $curl;
	protected Validator $validator;

	protected User $user;
	protected Post $post;

	protected $data_url = 'https://jsonplaceholder.typicode.com';

	function __construct(array $input, DBUtil $db, CURLUtil $curl, Validator $validator)
	{
		parent::__construct($input);
		$this->db = $db;
		$this->curl = $curl;
		$this->validator = $validator;
		$this->user = new User($db->get_connection());
		$this->post = new Post($db->get_connection());
		$this->post->debug = $this->user->debug = $this->curl->debug = DEBUG;
	}

	//--- API methods ---//

	public function fetch_remote_data(array $params)
	{

		$data = [
			'inserted_users' => 0,
			'inserted_posts' => 0,
		];
		$err = null;

		$url = $this->data_url;
		$res = $this->fetch_users($url);
		if (!($err = $res['error'])) {
			$data['inserted_users'] = $res['inserted'];
		}

		if ($data['inserted_users']) {
			$res = $this->fetch_posts($url);
			if (!($err = $res['error'])) {
				$data['inserted_posts'] = $res['inserted'];
			}
		}

		($err) ? $this->error($err) : $this->success($data);
	}

	public function create_user(array $params)
	{
		$data = $err = null;
		if (!($missing = $this->check_missing($params, ['user']))) {
			if ($this->validate_user($params['user'])) {
				$res = $this->user->create($params['user']);
				if (!($err = $res['error'])) {
					$data = $res['record'];
				}
			}
		}
		if ($missing) $this->params_error($missing);
		if ($err) $this->error($err);
		if ($error_bag = $this->validator->get_error_bag()) $this->validation_fail($error_bag);
		$this->success($data);
	}

	public function create_post(array $params)
	{
		try {
			$db = $this->db;
			$transaction = false;
			$err = null;
			$error_bag = null;

			$user = $params['user'];
			$user['id'] = (($user['id'] ?? 0) > 0) ? $user['id'] : null;
			$user['email'] = trim(($user['email'] ?? ''));

			$post = $params['post'];
			$post['id'] = null;

			if (!$user['id']) {
				$this->validate_user($user);
			}
			$this->validate_post($post);

			if (!($error_bag = $this->validator->get_error_bag())) {
				if (!$user['id']) {
					$db->begin_transaction();
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
				$this->validation_fail($error_bag);
			} elseif (!$post['id']) {
				if ($transaction) $db->rollback();
				$this->error($err);
			} else {
				if ($transaction) $db->commit();
				$this->success(compact('user', 'post'));
			}
		} catch (PDOException $e) {
			$db->db_exception($e);
		}
	}

	public function user_stats(array $params)
	{
		$res = $this->post->user_stats();
		if ($res['error']) {
			$this->error($res['error']);
		} else {
			$this->success($res['result']);
		}
	}
	//--- /API methods ---//


	protected function fetch_users($url)
	{
		$ret = ['inserted' => 0, 'error' => ''];
		$curl_res = $this->curl->get_content("$url/users");
		if ($curl_res['error']) {
			$ret['error'] = $curl_res['error'];
		} else {
			$data = json_decode($curl_res['result'], true);
			$db_res = $this->user->insert_batch($data);
			if ($db_res['error']) {
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
		if ($curl_res['error']) {
			$ret['error'] = $curl_res['error'];
		} else {
			$data = json_decode($curl_res['result'], true);
			$db_res = $this->post->insert_batch($data);
			if ($db_res['error']) {
				$ret['error'] = $db_res['error'];
			} else {
				$ret['inserted'] = $db_res['inserted'];
			}
		}
		return $ret;
	}

	protected function validate_user(array $user)
	{
		$rules = [
			'name:Name' => 'required|string',
			'email:Email' => 'required|unique:users.email',
		];
		return $this->validator->validate($user, $rules);
	}

	protected function validate_post(array $post)
	{
		$rules = [
			'title:Title' => 'required',
			'body:Body' => 'required',
		];
		return $this->validator->validate($post, $rules);
	}
}
