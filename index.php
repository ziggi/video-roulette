<?php

include_once 'bootstrap.php';

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
	<title>Video Roulette</title>
	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">
</head>
<body>
	<div id="info">
		<span>Video Roulette v1.0</span>
		<span><a href="https://github.com/ziggi/video-roulette" target="_blank">GitHub</a></span>
		<span><a href="http://ziggi.org/" target="_blank">Home</a></span>
	</div>
	<div id="middle">
		<div id="content">
			<video id="video_container" controls>
				<source type="video/webm">
			</video>
			<div class="clear"></div>
		</div>
		<div id="bottom">
			<div id="add" class="btn pull-left" title="Add">
				+
				<form id="form-input" method="post" enctype="multipart/form-data" action="upload.php">
					<input type="file" name="file" id="file-input">
				</form>
			</div>
			<div id="next" class="btn" title="Next">&#8680;</div>
			<div id="report" class="btn pull-right" title="Report">!</div>
		</div>
	</div>
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/alertify.core.css">
	<link rel="stylesheet" href="css/alertify.default.css">
	<script src="js/alertify.min.js"></script>
	<script src="js/upload.js"></script>
	<script src="js/app.js"></script>
</body>
</html>