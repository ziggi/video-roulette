window.addEventListener('load', function() {
	var currentVideo = null;

	// default volume
	if (localStorage.getItem('volume') === null || localStorage.getItem('muted') === null) {
		localStorage.setItem('muted', false);
		localStorage.setItem('volume', 0.5);
	}

	// report button
	document.getElementById('report').addEventListener('click', function() {
		if (currentVideo === null) {
			return;
		}

		var req = new XMLHttpRequest();
		req.open('POST', 'api/report.php', true);

		req.onreadystatechange = function () {
			if (req.readyState != 4 || req.status != 200) {
				return;
			}

			alertify.log(req.responseText);
		};

		req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

		req.send('hash=' + currentVideo.hash);
	});

	// load video
	var vid = document.getElementById("video_container");

	vid.addEventListener('volumechange', function() {
		localStorage.setItem('muted', this.muted);
		localStorage.setItem('volume', this.volume);
	});

	vid.addEventListener('ended', function() {
		loadVideo(this);
	});

	loadVideo(vid);

	document.getElementById('next').addEventListener('click', function() {
		loadVideo(vid);
	});

	document.getElementById('prev').addEventListener('click', function() {
		loadVideo(vid, 1);
	});

	function loadVideo(container, isprev) {
		isprev = typeof isprev !== 'undefined' ? isprev : 0;

		var req = new XMLHttpRequest();

		req.open('POST', 'api/getvideo.php', true);

		req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

		req.onreadystatechange = function () {
			if (req.readyState != 4 || req.status != 200) {
				return;
			}

			if (req.responseText.length == 0) {
				return;
			}

			currentVideo = JSON.parse(req.responseText);
			if (!currentVideo) {
				return;
			}

			container.getElementsByTagName('source')[0].src = currentVideo.hash + '.' + currentVideo.type;
			container.load();
			container.play();
			container.volume = localStorage.getItem('volume');
			container.muted = localStorage.getItem('muted') == 'true';
		};

		req.send('prev=' + isprev);
	}

	// load background
	loadBackground();
	setInterval(loadBackground, 60 * 1000);

	function loadBackground() {
		var req = new XMLHttpRequest();

		req.open('POST', 'api/getbackground.php', true);

		req.onreadystatechange = function () {
			if (req.readyState != 4 || req.status != 200) {
				return;
			}

			document.body.style.backgroundImage = "url('" + req.responseText + "')";
		};

		req.send();
	}
});
