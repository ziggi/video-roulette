<?php

include 'video_roulette.class.php';

$app = new VideoRoulette();

$result = $app->report($_POST['hash']);

if ($result) {
	echo "Reported";
} else {
	echo "Error report";
}