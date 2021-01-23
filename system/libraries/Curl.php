<?php

namespace System\Libraries;

class Curl
{
	protected $last_error = '';

	public function lastError(): string
	{
		return $this->last_error;
	}

	public function getContent(string $url): array
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);
		$error = $this->last_error = curl_error($ch);
		curl_close($ch);

		return ['result' => $result, 'error' => $error];
	}

	public function getStatus(string $url): int
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$this->last_error = curl_error($ch);
		curl_close($ch);

		return $status;
	}

	public function getContentType(string $url): string
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_exec($ch);
		$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		$this->last_error = curl_error($ch);
		curl_close($ch);

		return $content_type;
	}
}
