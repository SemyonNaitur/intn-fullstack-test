<?php

namespace App\Libraries\Api;

require_once app_config('libraries_path') . '/external/simplehtmldom_1_9_1/simple_html_dom.php';

use System\Libraries\{Db, Validator, Curl};
use System\Libraries\Api\ApiBase;

use App\Models\Assignments\NbmCrawler\Link;

class NbmCrawler extends ApiBase
{
	protected Db $db;
	protected Curl $curl;
	protected Validator $validator;

	protected Link $link;

	protected $opts = [
		'max_recursion' => 3,
	];

	protected $site_data;
	protected $recursion = 0;
	protected $paths = [
		'existing' => [],
		'scraped' => [],
		'inserted' => [],
	];

	function __construct(
		array $input,
		Db $db,
		Curl $curl,
		Validator $validator,
		Link $link
	) {
		parent::__construct($input);
		$this->db = $db;
		$this->curl = $curl;
		$this->validator = $validator;

		$this->link = $link;
	}

	//--- API methods ---//

	public function crawlSite(array $params)
	{
		$err = null;

		$site = $this->input['site'] ?? '';
		if (empty($site)) {
			$this->paramsError(['site']);
		}

		if ($this->validator::url_rule($site) !== true) {
			$err = 'Invalid site url';
		} else {
			$data = $this->site_data = parse_url($site);
			$this->site_data['base_url'] = ($data['scheme'] ?? 'http') . '://' . $data['host'];
			$this->paths['existing'] = $this->link->getBySite($site);
			$this->recursion = 0;
			set_time_limit(600);
			$this->scrape();
		}
		$data = $this->paths;

		($err) ? $this->error($err) : $this->success($data);
	}

	public function getSites(array $params)
	{
		$data = $this->link->getSites();
		$this->success($data);
	}

	public function getLinks(array $params)
	{
		$site = $this->input['site'] ?? '';
		if (empty($site)) {
			$this->paramsError(['site']);
		}

		$data = $this->link->getBySite($site);
		$this->success($data);
	}

	public function deleteLinks(array $params)
	{
		$site = $this->input['site'] ?? '';
		if (empty($site)) {
			$this->paramsError(['site']);
		}

		$data = ['rows_deleted' => $this->link->deleteBySite($site)];
		$this->success($data);
	}
	//--- /API methods ---//


	protected function scrape(string $path = '')
	{
		$path = trim($path, '/');
		if (in_array($path, $this->paths['scraped'])) return;

		$base_url = $this->site_data['base_url'];
		$url = $base_url . '/' . $path;
		$content_type = $this->getContentType($url);

		if (
			$this->isAllowedContentType($content_type)
			&& $html = $this->getHtml($url)
		) {
			foreach ($html->find('a') as $a) {
				$link_data = parse_url($a->href);

				if (!empty($link_data['host'])) {
					if (
						$link_data['host'] != $this->site_data['host'] // external
						|| !$this->isAllowedScheme($link_data['scheme'] ?? '')
					) continue;
				}

				$link_path = ($link_data['path'] ?? '') ?: '/';
				if ($link_path != '/') {

					$link_url = $base_url . $link_path;
					if ($this->curl->getStatus($link_url) == 200) {

						if (!$this->isDuplicate($link_path)) {
							$this->insertPath($link_path);
						}

						if ($this->recursion < $this->opts['max_recursion']) {
							$this->recursion++;
							$this->scrape($link_path);
							$this->recursion--;
						}
					}
				}
			}
		}

		$this->paths['scraped'][] = $path;
	}

	protected function getHtml($url)
	{
		try {
			$html = file_get_html($url);
			log_debug(str_repeat("\t", $this->recursion) . "$url: fetched");
			return $html;
		} catch (\Throwable $e) {
			return null;
		}
	}

	protected function getContentType(string $url)
	{
		$type = $this->curl->getContentType($url);
		$type = explode(' ', $type)[0];
		return strtolower(trim($type, ';'));
	}

	protected function isDuplicate(string $path): bool
	{
		return in_array($path, $this->paths['existing']) || in_array($path, $this->paths['inserted']);
	}

	protected function isAllowedScheme(string $scheme): bool
	{
		return in_array($scheme, ['', 'http', 'https']);
	}

	protected function isAllowedContentType(string $type): bool
	{
		return in_array($type, ['text/html']);
	}

	protected function insertPath(string $path): void
	{
		$this->link->create([
			'siteUrl' => $this->site_data['base_url'],
			'linkPath' =>  $path,
		]);
		$this->paths['inserted'][] = $path;
		log_debug(str_repeat("\t", $this->recursion) . "$path: inserted");
	}
}
