<?php

$conf = include '../config.php';

include '../include/video_roulette.class.php';

// access for remote uploads
header('Access-Control-Allow-Origin: *');

// upload file
$app = new VideoRoulette($conf);

$app->upload_file($_FILES);
