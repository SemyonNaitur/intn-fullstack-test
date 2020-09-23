<?php
require_once UTILS_DIR . '/ApiBase.php';
require_once UTILS_DIR . '/Validator.php';

require_once MODELS_DIR . '/Post.php';
require_once MODELS_DIR . '/User.php';

class BlogApi extends ApiBase
{
	protected DBUtil $db;
	protected CURLUtil $curl;

	protected User $user;
	protected Post $post;

	protected $data_url = 'https://jsonplaceholder.typicode.com';

	function __construct(array $input, DBUtil $db, CURLUtil $curl)
	{
		parent::__construct($input);
		$this->db = $db;
		$this->curl = $curl;
		$this->user = new User($db->get_connection());
		$this->post = new Post($db->get_connection());
		$this->post->debug = $this->user->debug = $this->curl->debug = DEBUG;
	}

	//--- API methods ---//

	public function fetch_remote_data(array $params, ApiResponse $resp)
	{
		$url = $this->data_url;

		$data = [
			'inserted_users' => 0,
			'inserted_posts' => 0,
		];
		$err = '';

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

		if ($err) {
			$this->error($err);
		} else {
			$this->success($data);
		}
	}

	public function create_user(array $params, ApiResponse $resp)
	{
		if ($this->validate_user($params['user'])) {
			$res = $this->user->create($params['user']);
			if ($res['error']) {
				$this->error($res['error']);
			} else {
				$this->success($res['user']);
			}
		}
	}

	public function create_post(array $params, ApiResponse $resp)
	{
		try {
			$db = $this->db;
			$transaction = false;

			$user = $params['user'];
			$user['id'] = (($user['id'] ?? 0) > 0) ? $user['id'] : null;
			$user['email'] = trim(($user['email'] ?? ''));

			$post = $params['post'];
			unset($post['id']);

			$err = '';

			if (!$user['id']) {
				$db->begin_transaction();
				$transaction = true;

				if (!$this->validate_user($user)) {
					$res = $this->user->create($user);
					if (!($err = $res['error'])) {
						$user = $res['record'];
					}
				}
			}

			if (!$err) {
				$post['userId'] = ($user['id'] > 0) ? $user['id'] : null;

				if (!$this->validate_post($post)) {
					$res = $this->post->create($post);
					if (!($err = $res['error'])) {
						$post = $res['record'];
					}
				}
			}

			if (empty($post['id'])) {
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

	public function user_stats(array $params, ApiResponse $resp)
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
		$error_bag = [];
		$rules = [
			'name' => 'required|string',
			'email' => 'required|string',
		];
		if (($valid = Validator::validate($user, $rules)) !== true) {
			$error_bag = $this->validation_fail($valid);
		}
		if (empty($error_bag['email'])) {
			if (!$this->user->is_unique($user['email'], 'email')) {
				$error_bag = $this->validation_fail(['email' => ['Email already exists']]);
			}
		}
		return $error_bag;
	}

	protected function validate_post(array $post)
	{
		$error_bag = [];
		$rules = [
			'userId' => 'required|integer',
			'title' => 'required',
			'body' => 'required',
		];
		if (($valid = Validator::validate($post, $rules)) !== true) {
			$error_bag = $this->validation_fail($valid);
		}
		return $error_bag;
	}
}
