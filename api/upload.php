<?php

$conf = include '../config.php';

include '../include/video_roulette.class.php';

// access for remote uploads
header('Access-Control-Allow-Origin: *');

// upload file
$app = new VideoRoulette($conf['file_dir'], $conf['db']['host'], $conf['db']['base'], $conf['db']['user'], $conf['db']['pass']);

$app->upload_file($_FILES);
