<?php

$conf = include '../config.php';

$directory = 'img/background/';

$scanned_directory = array_diff(scandir('../' . $directory), array('..', '.'));
$key = array_rand($scanned_directory);

echo $conf['uri'] . '/' . $directory . $scanned_directory[$key];
