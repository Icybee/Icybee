<?php

$src = $_SERVER['argv'][1];

if (!function_exists('curl_init'))
{
	echo <<<EOT

Unable to call the online compressor, cURL is not installed!
You can install its package using the following line:

apt-get install php5-curl


EOT;

	exit(-1);
}

$ch = curl_init('http://marijnhaverbeke.nl/uglifyjs');

curl_setopt_array
(
	$ch, array
	(
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => http_build_query
		(
			array
			(
				'js_code' => file_get_contents($src),
				'utf8' => 1
			)
		)
	)
);

curl_exec($ch);