<?php

namespace System\Libraries;

class Curl
{
	public $debug = false;

	public function getContent(string $url): array
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);
		$error = curl_error($ch);
		curl_close($ch);

		return compact('result', 'error');
	}

	public function getStatus(string $url): array
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);

		return compact('status', 'error');
	}

	public function getContentType(string $url): array
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_exec($ch);
		$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		$error = curl_error($ch);
		curl_close($ch);

		return compact('content_type', 'error');
	}
}
