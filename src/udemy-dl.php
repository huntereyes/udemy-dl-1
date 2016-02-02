<?php
	ini_set('max_execution_time', 0);
	set_time_limit(0);

	require dirname(__FILE__) . '/urls.php';
	require dirname(__FILE__) . '/functions.php';

	$start = 0;
	$end = null;
	$list = null;
	$basedir = dirname(__FILE__) . DIRECTORY_SEPARATOR . "download";
	$supported_downloaders = ["wget","idm"];
	$downloader = "wget";
	$cookiefolder = dirname(__FILE__) . DIRECTORY_SEPARATOR . "cookies";
	if (!file_exists($cookiefolder)) mkdir($cookiefolder);

	$shortopts = "i:u:s:e:l:f:d:h";
	$longopts = array(
		"id:",
		"url:",
		"start:",
		"end:",
		"list:",
		"folder:",
		"downloader:",
		"help"
	);

	$options = getopt($shortopts, $longopts);

	get_cli_option($options,"help","h") === false ? print_help() : "" ;
		
	if ((!isset($options["id"]) && !isset($options["i"])) && (!isset($options["url"]) && !isset($options["u"]))) {
		echo "ERROR: Please set either course ID or URL.\n";
		print_help();
	}
	else
	if ((isset($options["id"]) || isset($options["i"])) && (isset($options["url"]) || isset($options["u"]))) {
		echo "Course ID set. URL will be ignored.\n";
	}

	if (isset($options["id"]) || isset($options["i"])) {
		$courseId = isset($options["id"]) ? $options["id"] : $options["i"];
	}
	else
	if (isset($options["url"]) || isset($options["u"])) {
		$courseURL = isset($options["url"]) ? $options["url"] : $options["u"];
	}

	if (isset($options["start"]) || isset($options["s"])) {
		$start = isset($options["start"]) ? $options["start"] : $options["s"];
	}
	if (isset($options["end"]) || isset($options["e"])) {
		$end = isset($options["end"]) ? $options["end"] : $options["e"];
	}
	if (isset($options["list"]) || isset($options["l"])) {
		$list = isset($options["list"]) ? $options["list"] : $options["l"];
	}

	if (isset($options["downloader"]) || isset($options["d"])) {
		$downloader = strtolower(isset($options["downloader"]) ? $options["downloader"] : $options["d"]);
		if(!in_array($downloader,$supported_downloaders)) {
			echo "Invalid downloader specified. Supported downloaders: ".implode(", ", $supported_downloaders)."\n";
			echo "Choosing default downloader wget\n";
			$downloader = "wget";
		}
	}

	if (isset($options["folder"]) || isset($options["f"])) {
		$basedir = isset($options["folder"]) ? $options["folder"] : $options["f"];
	}

	if(!empty($list)){
		$list = explode(",", $list);
		echo "List specified. Start and End indexes will be ignored\n";
	}

	if(!isset($courseId) && isset($courseURL)) {
		echo "Fetching course ID....\n";
		$coursePage['options'] = $urls['coursePage']['options'];
		$coursePage['response'] = common_curl($courseURL, $urls['coursePage']['method'], $coursePage['options']);
		preg_match('/data-course-id="(\d+)"/',$coursePage['response']['body'], $coursePage['courseId']);
		if($coursePage['response']['status'] != 200 || empty($coursePage['courseId']) || empty($coursePage['courseId'][1]))
			die("Error fetching course ID. Invalid URL. Please try entering course ID directly.");
		$courseId = $coursePage['courseId'][1];
	}

	$id = time()."_".unique_id();
	$user = [
	'name' => "John Doe",
	'email' => "john_doe_".$id."@eyepaste.com",
	'cookiefile' => $cookiefolder.DIRECTORY_SEPARATOR.$id.".txt",
	'password' => "johndoe"
	];

	// ***********************************************
	//	              Registration
	// ***********************************************

	echo "Registering....\n";
	$register['options'] = $urls['register']['options'];
	$register['options']['body'] = ['fullname' => $user['name'], 'email' => $user['email'], 'password' => $user['password'], 'timezone' => 'Asia/Calcutta', 'is_generated' => '0', 'locale' => 'en_US'];
	$register['response'] = common_curl($urls['register']['link'], $urls['register']['method'], $register['options']);
	$register['decoded_response'] = json_decode($register['response']['body'], true);
	$user['accesstoken'] = $register['decoded_response']['access_token'];

	if(empty($user['accesstoken']))
		die("Failed to register user");
	else
		echo "Registration successful\n";

	// ***********************************************
	//	                 Login
	// ***********************************************

	echo "Logging in....\n";
	$login['options'] = $urls['login']['options'];
	$login['options']['cookiefile'] = $user['cookiefile'];
	$login['response'] = common_curl($urls['login']['link'], $urls['login']['method'], $login['options']);

	if ($login['response']['status'] == 302 && in_array("Location: https://www.udemy.com/", $login['response']['headers'])) {
		$login['loggedin'] = true;
		echo "Login successful\n";
	}
	else {
		preg_match('/<form.*?action="(https:\/\/www\.udemy\.com\/join\/.*?)"/', $login['response']['body'], $login['form_action']['matches']);
		$login['form_action'] = $login['form_action']['matches'][1];
		preg_match('/<input.*?name=\'csrfmiddlewaretoken\' value=\'(.*?)\'/', $login['response']['body'], $login['csrftoken']['matches']);
		$login['csrftoken'] = $login['csrftoken']['matches'][1];
		if ($login['form_action'] && $login['csrftoken']) {
			$login['options'] = $urls['login']['options'];
			$login['options']['body'] = 'csrfmiddlewaretoken=' . $login['csrftoken'] . '&locale=en_US&email=' . $user['email'] . '&password=' . $user['password'] . '&submit=Login';
			$login['options']['cookiefile'] = $user['cookiefile'];
			$login['response'] = common_curl($login['form_action'], 'POST', $login['options']);
			preg_match('/<div.*?class="form-errors.*?"><ul>(<li>.*?<\/li>)<\/ul>/', $login['response']['body'], $login['errors']);
			if (!empty($login['errors'])) {
				die("Invalid Credentials");
			}
			else echo "Login successful\n";
		}
		else die("Error fetching login page");
	}

	// ***********************************************
	//                  Subscribe
	// ***********************************************

	echo "Subscribing to course....\n";
	$subscribe['options'] = $urls['subscribePreview']['options'];
	$subscribe['options']['cookiefile'] = $user['cookiefile'];
	$subscribe = common_curl(str_replace(':courseId', $courseId, $urls['subscribePreview']['link']) , $urls['subscribePreview']['method'], $subscribe['options']);

	// ***********************************************
	//               Fetch Curriculum
	// ***********************************************

	echo "Fetching course curriculum....\n";
	$course['options'] = $urls['course']['options'];
	$course['options']['headers'] = array_merge($course['options']['headers'], ['X-Udemy-Bearer-Token: ' . $user['accesstoken']]);
	$course['link'] = str_replace(':courseId', $courseId, $urls['course']['link']);
	$course['response'] = common_curl($course['link'], $urls['course']['method'], $course['options']);
	$course['extras'] = true;

	if ($course['response']['status'] != 200) {
		$course['response'] = common_curl(str_replace('extras,', '', $course['link']) , $urls['course']['method'], $course['options']);
		$course['extras'] = false;
	}

	if ($course['response']['status'] != 200) die("Error fetching course details");
	$course['decoded_response'] = json_decode($course['response']['body'], true);
	echo "Parsing links....\n";
	$course['download_links'] = parse_links($courseId, $course['decoded_response'], $course['extras']);	

	// ***********************************************
	//                Download Course
	// ***********************************************

	echo "Downloading course....\n";

	if (!file_exists($basedir)) mkdir($basedir);
	download_folder($basedir, $course['download_links'], $start, $end, $list, $downloader);
	switch ($downloader) {
		case 'idm':
			exec("idman /s");
			echo "Course added to IDM and downloading started.\n";
			break;
		default:
			echo "Course download complete.\n";
			break;
	}
	
?>