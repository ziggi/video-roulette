<?php

$conf = include 'config.php';

if (empty($conf['uri_param'])) {
	return;
}

$file_name = $conf['uri_param'];
$file_path = 'file/' . $file_name[0] . '/' . $file_name[1] . '/' . $file_name;

$is_valid_file = preg_match('/^[0-9a-f]{32}\.[a-z]+$/', $file_name) == 1;

if ($is_valid_file && file_exists($file_path)) {
	header('Content-type: video/webm');

	readfile($file_path);
} else {
	header('HTTP/1.0 404 Not Found');
	echo '404';
	exit;
}