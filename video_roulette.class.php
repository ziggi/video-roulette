<?php

class VideoRoulette {
	private static $_db_info = array(
			'host' => 'localhost',
			'username' => 'root',
			'passwd' => 'root',
			'dbname' => 'test'
		);

	const MAX_FILE_SIZE = '15M';
	const MAX_REPORTS_IN_DAY = 10;

	private $_allowed_types = array(
			'video/webm' => array('file_format' => 'webm'),
		);

	private $db;

	function __construct() {
		$this->db = new mysqli(self::$_db_info['host'], self::$_db_info['username'], self::$_db_info['passwd'], self::$_db_info['dbname']);

		if ($this->db->connect_errno) {
			exit();
		}
	}

	public function get_random_file($is_prev = false) {
		session_start();

		if (isset($_SESSION['current'])) {
			if ($is_prev) {
				if (--$_SESSION['current'] < 0) {
					$_SESSION['current'] = count($_SESSION['arr']) - 1;
				}
			} else {
				if (++$_SESSION['current'] >= count($_SESSION['arr'])) {
					$_SESSION['current'] = 0;
				}
			}
		} else {
			$_SESSION['current'] = 0;
		}

		if (!isset($_SESSION['arr'])) {
			$arr = array();

			$query = "	SELECT
							`hash`,
							`type`
						FROM
							`video_info`
							LEFT JOIN `video_report` on
								`video_report`.`video_id` = `video_info`.`id`
						GROUP BY
							`video_info`.`id`
						HAVING
							count(`video_report`.`id`) < 5
						";

			if ($result = $this->db->query($query)) {
				while ($row = $result->fetch_row()) {
					$arr[] = array('hash' => $row[0], 'type' => $row[1]);
				}

				$result->close();
			}
			shuffle($arr);

			$_SESSION['arr'] = $arr;
		}

		echo json_encode($_SESSION['arr'][ $_SESSION['current'] ]);
	}

	public function upload_file($files) {
		$array_result = array();

		// error checking
		$array_result['error']['upload'] = 0;
		$array_result['error']['type'] = 0;
		$array_result['error']['size'] = 0;
		$array_result['error']['exist'] = 0;

		if (!is_array($files) || !isset($files['file'])) {
			$array_result['error']['upload'] = 1;
			echo json_encode($array_result);
			return;
		}

		$file = $files['file'];

		if ($file['error'] === 1) {
			$array_result['error']['upload'] = 1;
		}

		$type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file['tmp_name']);
		if (!$this->is_support_type($type)) {
			$array_result['error']['upload'] = 1;
			$array_result['error']['type'] = 1;
		}

		if (!$this->is_support_size($file['size'])) {
			$array_result['error']['upload'] = 1;
			$array_result['error']['size'] = 1;
		}

		$array_result['hash'] = md5_file($file['tmp_name']);
		if ($this->is_file_exist($array_result['hash'])) {
			$array_result['error']['upload'] = 1;
			$array_result['error']['exist'] = 1;
		}

		if ($array_result['error']['upload'] == 1) {
			echo json_encode($array_result);
			return;
		}

		// generate new name
		$array_result['type'] = $this->_allowed_types[ $type ]['file_format'];
		$new_name = $array_result['hash'] . '.' . $array_result['type'];
		$file_path = __DIR__ . '/file/' . $new_name[0] . '/' . $new_name[1] . '/';

		// move temp file with new name
		if (!file_exists($file_path)) {
			mkdir($file_path, 0777, true);
		}
		move_uploaded_file($file['tmp_name'], $file_path . $new_name);

		// 
		$this->db->query("INSERT INTO `video_info` (`hash`, `type`) VALUES ('" . $array_result['hash'] . "', '" . $array_result['type'] . "')");

		// 
		echo json_encode($array_result);
	}

	public function report($file_hash) {
		if (!$this->is_valid_hash($file_hash)) {
			return false;
		}

		// get file id
		$file_id = null;

		if ($result = $this->db->query("SELECT `id` FROM `video_info` WHERE `hash` = '" . $file_hash . "'")) {
			$row = $result->fetch_row();
			$file_id = $row[0];

			$result->close();
		}

		// get ip
		$ip = 0;

		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		$ip = ip2long($ip);

		// get count
		$query = "	SELECT 
						count(`id`)
					FROM
						`video_report`
					WHERE
						`ip` = " . $ip . " AND
						`video_id` = $file_id AND
						`date` > CURRENT_TIMESTAMP - 86400
					";

		if ($result = $this->db->query($query)) {
			$row = $result->fetch_row();
			$count = $row[0];
			
			$result->close();

			if ($count > 0) {
				return false;
			}
		}

		// get count on ip
		$query = "	SELECT 
						count(`id`)
					FROM
						`video_report`
					WHERE
						`ip` = " . $ip . " AND
						`date` > CURRENT_TIMESTAMP - 86400
					";

		if ($result = $this->db->query($query)) {
			$row = $result->fetch_row();
			$count = $row[0];
			
			$result->close();

			if ($count > self::MAX_REPORTS_IN_DAY) {
				return false;
			}
		}

		// add
		$this->db->query("INSERT INTO `video_report` (`video_id`, `ip`) VALUES (" . $file_id . ", " . $ip . ")");
		return true;
	}

	public function is_valid_hash($file_hash) {
		return preg_match('/^[0-9a-f]{32}$/', $file_hash) == 1;
	}

	public function is_support_size($size) {
		$is_app_support_size = $size <= $this->return_bytes(self::MAX_FILE_SIZE);
		$is_php_support_size = $size <= $this->return_bytes(ini_get('upload_max_filesize'));
		$is_post_support_size = $size <= $this->return_bytes(ini_get('post_max_size'));

		if ($size === false || !$is_app_support_size || !$is_php_support_size || !$is_post_support_size) {
			return false;
		}

		return true;
	}

	public function is_support_type($type) {
		if (!isset($this->_allowed_types[$type])) {
			return false;
		}
		return true;
	}

	public function is_file_exist($file_hash) {
		$is_exist = false;

		if ($result = $this->db->query("SELECT count(`id`) FROM `video_info` WHERE `hash` = '" . $file_hash . "'", MYSQLI_USE_RESULT)) {
			$row = $result->fetch_row();
			if ($row[0] > 0) {
				$is_exist = true;
			}

			$result->close();
		}

		return $is_exist;
	}

	public function return_bytes($val) {
		$val = trim($val);
		$last = strtolower($val[ strlen($val) - 1 ]);

		switch ($last) {
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}
} 
