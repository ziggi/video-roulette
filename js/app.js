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
		req.open('POST', 'report.php', true);

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

	function loadVideo(container) {
		var req = new XMLHttpRequest();

		req.open('POST', 'getvideo.php', true);

		req.onreadystatechange = function () {
			if (req.readyState != 4 || req.status != 200) {
				return;
			}

			currentVideo = JSON.parse(req.responseText);
			container.getElementsByTagName('source')[0].src = currentVideo.hash + '.' + currentVideo.type;
			container.load();
			container.play();
			container.volume = localStorage.getItem('volume');
			container.muted = localStorage.getItem('muted') == 'true';
		};

		req.send();
	}

	// load background
	loadBackground();
	setInterval(loadBackground, 60 * 1000);

	function loadBackground() {
		var req = new XMLHttpRequest();

		req.open('POST', 'getbackground.php', true);

		req.onreadystatechange = function () {
			if (req.readyState != 4 || req.status != 200) {
				return;
			}

			document.body.style.backgroundImage = "url('" + req.responseText + "')";
		};

		req.send();
	}
});