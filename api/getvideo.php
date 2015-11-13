<?php

$conf = include '../config.php';

include '../include/video_roulette.class.php';

$app = new VideoRoulette($conf['file_dir'], $conf['db']['host'], $conf['db']['base'], $conf['db']['user'], $conf['db']['pass']);

$is_prev = isset($_POST['prev']) && (int)$_POST['prev'] === 1;

$app->get_random_file($is_prev);
