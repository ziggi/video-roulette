<?php

include 'video_roulette.class.php';

$app = new VideoRoulette();

$is_prev = isset($_POST['prev']) && (int)$_POST['prev'] === 1;

$app->get_random_file($is_prev);