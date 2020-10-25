<?php

namespace System;

class CURLUtil
{
	public $debug = false;

	public function get_content(string $url)
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
