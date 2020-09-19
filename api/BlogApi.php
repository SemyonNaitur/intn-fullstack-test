<?php
require_once UTILS_DIR . '/ApiBase.php';

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

		$resp->data = [
			'inserted_users' => 0,
			'inserted_posts' => 0,
		];

		$res = $this->fetch_users($url);
		if ($res['error']) {
			$resp->status = 'ERR';
			$resp->message = $res['error'];
		} else {
			$resp->data['inserted_users'] = $res['inserted'];
		}

		if ($resp->data['inserted_users']) {
			$res = $this->fetch_posts($url);
			if ($res['error']) {
				$resp->status = 'ERR';
				$resp->message = $res['error'];
			} else {
				$resp->data['inserted_posts'] = $res['inserted'];
			}
		}
	}

	public function create_user(array $params, ApiResponse $resp)
	{
		$res = $this->user->create($params['user']);
		if (isset($res['error_bag'])) {
			$this->validation_fail($res['error'], $res['error_bag']);
		} elseif ($res['error']) {
			$resp->status = 'ERR';
			$resp->message = $res['error'];
		} else {
			$resp->data['user'] = $res['user'];
		}
	}

	public function create_post(array $params, ApiResponse $resp)
	{
		$db = $this->db;
		$transaction = false;

		$user = $params['user'];
		$user['id'] ??= 0;

		$post = $params['post'];
		unset($post['id']);

		try {
			if (!$user['id']) {
				$db->begin_transaction();
				$transaction = true;
				$res = $this->user->create($user);
				if (isset($res['error_bag'])) {
					$this->validation_fail($res['error'], $res['error_bag']);
				} elseif ($res['error']) {
					$resp->status = 'ERR';
					$resp->message = $res['error'];
				} else {
					$user = $res['record'];
				}
			}
			$post['userId'] = $user['id'];

			// Post::create is called even if User::create failed - for data validation.
			$res = $this->post->create($post);
			if (isset($res['error_bag'])) {
				$this->validation_fail($res['error'], $res['error_bag']);
			} elseif ($res['error']) {
				$resp->status = 'ERR';
				$resp->message = $res['error'];
			} else {
				$post = $res['record'];
				$resp->data = [
					'user' => $user,
					'post' => $post
				];
			}

			if (empty($post['id'])) {
				if ($transaction) $db->rollback();
			} else {
				if ($transaction) $db->commit();
				$resp->data = [
					'user' => $user,
					'post' => $post
				];
			}
		} catch (PDOException $e) {
			$db->db_exception($e);
		}
	}

	public function posts_by_id(array $params, ApiResponse $resp)
	{
		$res = $this->post->search_by_id($params['id']);
		if ($res['error']) {
			$resp->status = 'ERR';
			$resp->message = $res['error'];
		} else {
			$resp->data = $res['result'];
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
}
