<?php

include 'video_roulette.class.php';

// access for remote uploads
header('Access-Control-Allow-Origin: *');

// upload file
$app = new VideoRoulette();

$app->upload_file($_FILES);