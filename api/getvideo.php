<?php

$conf = include '../config.php';

include '../include/video_roulette.class.php';

$app = new VideoRoulette($conf);

$is_prev = isset($_POST['prev']) && (int)$_POST['prev'] === 1;

$app->get_random_file($is_prev);
