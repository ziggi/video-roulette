window.addEventListener('load', function() { 
	document.getElementById('add').addEventListener('click', function() {
		document.getElementById('file-input').click();
	});

	document.getElementById('file-input').addEventListener('change', function(e) {
		var req = new XMLHttpRequest();
		req.open('POST', 'upload.php', true);

		req.onreadystatechange = function () {
			if (req.readyState != 4 || req.status != 200) {
				return;
			}

			var result = JSON.parse(req.responseText);
			if (result.error.upload == 0) {
				alertify.log("File has been uploaded");
			} else {
				var errorText = 'Error: ';

				if (result.error.type == 1) {
					errorText += 'bad type (webm only)';
					errorText += ', ';
				}

				if (result.error.size == 1) {
					errorText += 'bad size (max 15 MiB)';
					errorText += ', ';
				}

				if (result.error.exist == 1) {
					errorText += 'file exists';
					errorText += ', ';
				}

				if (result.error.type == 0 && result.error.size == 0 && result.error.exist == 0) {
					errorText += 'unknown';
				} else {
					errorText = errorText.slice(0, errorText.length - 2);
				}

				alertify.log(errorText);
			}
		};

		var formData = new FormData();
		formData.append('file', document.getElementById('file-input').files[0]);
		req.send(formData);

		alertify.log("Uploading...");
	});
});