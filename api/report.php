<?php

$conf = include '../config.php';

include '../include/video_roulette.class.php';

$app = new VideoRoulette($conf['file_dir'], $conf['db']['host'], $conf['db']['base'], $conf['db']['user'], $conf['db']['pass']);

$result = $app->report($_POST['hash']);

if ($result) {
	echo "Reported";
} else {
	echo "Error report";
}
