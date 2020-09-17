<?php

class CURLUtil
{
	public $debug = false;

	public function get_content(array $opts)
	{
		$defaults = [
			CURLOPT_URL            => '',
			CURLOPT_RETURNTRANSFER => true
		];
		$opts = array_merge($defaults, $opts);

		$ch = curl_init();
		curl_setopt_array($ch, $opts);

		$result = curl_exec($ch);
		$error = curl_error($ch);
		curl_close($ch);

		return ['result' => $result, 'error' => $error];
	}
}
