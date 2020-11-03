<?php

namespace System;

class Curl
{
	public $debug = false;

	public function getContent(string $url): array
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);
		$error = curl_error($ch);
		curl_close($ch);

		return ['result' => $result, 'error' => $error];
	}
}
