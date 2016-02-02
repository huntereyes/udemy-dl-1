<?php
	function common_curl($url, $method, $options = [])
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.86 Safari/537.36'); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		if($method === 'POST')
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $options['body']);			
		}

		if(!empty($options['cookie']))
		{
			curl_setopt ($ch, CURLOPT_COOKIEJAR, $options['cookiefile']); 
       		curl_setopt ($ch, CURLOPT_COOKIEFILE, $options['cookiefile']); 
		}
		
		if(!empty($options['headers']))
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']); 
		}

		if(!empty($options['redirect']))
		{
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
		}

		$body = curl_exec($ch);
		$info = curl_getinfo($ch);

		$headers = substr($body, 0, $info['header_size']);

		$response = substr($body, $info['header_size']);

      	curl_close($ch); 

      	return ['status' => $info['http_code'], 'body' => $response, 'headers' => explode("\r\n", $headers)];
	}

	function unique_id($l = 8) {
    	return substr(md5(uniqid(mt_rand(), true)), 0, $l);	
	}

	function parse_links($courseId, $data, $extras = true)
	{
		$chapter_contents = array();
		$chapter_contents[0] = array();
		$current_chapter = 0;
		$final_data = array('type'=>'folder',
							'title'=>safe_filename($courseId));
		$first_chapter = true;
		$offset = 0;

		foreach($data as $item)
		{
			if($item["__class"]=="chapter")
			{
				if($first_chapter)
				{
					$final_data['contents'] = array();
					if(count($chapter_contents[0])>0)
					{
						$final_data['contents'] = $chapter_contents[0];
					}
					$offset = count($chapter_contents[0]);
					$first_chapter = false;
				}
				else
				{
					$final_data['contents'][$current_chapter+$offset-1]['contents'] = $chapter_contents[$current_chapter];
				}
				$current_chapter++;
				$chapter_contents[$current_chapter] = array();
				$chapter = array('type'=>'folder',
								'title'=>safe_filename($item["title"]),
								'index'=>$item["index"]);
				array_push($final_data['contents'], $chapter);
			}
			else if($item["__class"]=="lecture")
			{
				if($item["assetType"]=="Article")
				{
					$asset = array('type'=>'file',
								'data'=>'text',
								'extension'=>'html',
								'title'=>safe_filename($item["title"]),
								'index'=>$item["lectureIndex"],
								'contents'=>$item['asset']["data"]["body"]);
					array_push($chapter_contents[$current_chapter],$asset);
				}
				else if($item["assetType"]=="Presentation")
				{
					if(!empty($item['asset']['downloadUrl']['download']))
					{
						$asset = array('type'=>'file',
									'data'=>'link',
									'extension'=> end(explode(".", strtolower($item['asset']['data']['name']))),
									'title'=>safe_filename($item["title"]),
									'index'=>$item["lectureIndex"],
									'contents'=>$item['asset']['downloadUrl']['download']);
						array_push($chapter_contents[$current_chapter],$asset);
					}
					else
					{
						$asset = array('type'=>'folder',
									'title'=>safe_filename($item["title"]),
									'index'=>$item["lectureIndex"],
									'contents'=>array());
						preg_match('/src="https:\/\/www.udemy.com\/embed\/presentation\/(.*?)\/" width/',$item['asset']['viewHTML'],$matches);
						$asset['contents'] = get_links('presentation',$matches[1]);
						//array_push($chapter_contents[$current_chapter],$asset);
					}	
				}
				else if($item["assetType"]=="VideoMashup")
				{
					if(!empty($item['asset']['downloadUrl']['download']))
					{
						$asset = array('type'=>'file',
									'data'=>'link',
									'extension'=> 'mp4',
									'title'=>safe_filename($item["title"]),
									'index'=>$item["lectureIndex"],
									'contents'=>$item['asset']['downloadUrl']['Video']['0']);
						array_push($chapter_contents[$current_chapter],$asset);

						$asset = array('type'=>'file',
									'data'=>'link',
									'extension'=> 'pdf',
									'title'=>safe_filename($item["title"]),
									'index'=>$item["lectureIndex"],
									'contents'=>$item['asset']['downloadUrl']['download']);
						array_push($chapter_contents[$current_chapter],$asset);
					}
					else
					{
						$asset = array('type'=>'file',
									'data'=>'link',
									'extension'=>'mp4',
									'title'=>safe_filename($item["title"]),
									'index'=>$item["lectureIndex"]);
						preg_match('/src="(.*?)\/" width/',$item['asset']['viewHTML'],$matches);
						$asset['contents'] = get_links('videomashup',$matches[1]);
						array_push($chapter_contents[$current_chapter],$asset);
					}
				}
				else if($item["assetType"]=="Video")
				{
					if(!empty($item['asset']['downloadUrl']['download']))
					{
						$asset = array('type'=>'file',
									'data'=>'link',
									'extension'=> end(explode(".", strtolower($item['asset']['data']['name']))),
									'title'=>safe_filename($item["title"]),
									'index'=>$item["lectureIndex"],
									'contents'=>$item['asset']['downloadUrl']['download']);
						array_push($chapter_contents[$current_chapter],$asset);
					}
					else
					{
						$asset = array('type'=>'file',
									'data'=>'link',
									'extension'=>'mp4',
									'title'=>safe_filename($item["title"]),
									'index'=>$item["lectureIndex"]);
						preg_match('/src="(.*?)\/" width/',$item['asset']['viewHTML'],$matches);
						$asset['contents'] = get_links('video',$matches[1]);
						array_push($chapter_contents[$current_chapter],$asset);
					}
				}
				else if($item["assetType"]=="E-Book")
				{
					if(!empty($item['asset']['downloadUrl']['download']))
					{
						$asset = array('type'=>'file',
									'data'=>'link',
									'extension'=> end(explode(".", strtolower($item['asset']['data']['name']))),
									'title'=>safe_filename($item["title"]),
									'index'=>$item["lectureIndex"],
									'contents'=>$item['asset']['downloadUrl']['download']);
						array_push($chapter_contents[$current_chapter],$asset);
					}
					else
					{
						$asset = array('type'=>'folder',
									'title'=>safe_filename($item["title"]),
									'index'=>$item["lectureIndex"],
									'contents'=>array());
						preg_match('/src="https:\/\/www.udemy.com\/embed\/e-book\/(.*?)\/" width/',$item['asset']['viewHTML'],$matches);
						$asset['contents'] = get_links('e-book',$matches[1]);
						//array_push($chapter_contents[$current_chapter],$asset);
					}
				}
				else
				{
					if(!empty($item['asset']['downloadUrl']['download']))
					{
						$asset = array('type'=>'file',
									'data'=>'link',
									'extension'=> end(explode(".", strtolower($item['asset']['data']['name']))),
									'title'=>safe_filename($item["title"]),
									'index'=>$item["lectureIndex"],
									'contents'=>$item['asset']['downloadUrl']['download']);
						array_push($chapter_contents[$current_chapter],$asset);
					}
				}

				if($extras)
				{
					if(count($item["extras"]) > 0)
					{
						$actual_count=0;
						$extras_folder = array('type'=>'folder',
											'title'=> "Extras",
											'index'=>$item["lectureIndex"]);
						$extras_content = array();
						foreach ($item["extras"] as $key => $extra)
						{
							if(!empty($extra['downloadUrl']['download']))
							{
								$actual_count++;
								$asset = array('type'=>'file',
											'data'=>'link',
											'extension'=> end(explode(".", strtolower($extra['data']['name']))),
											'title'=> safe_filename($extra["title"]),
											'index'=> ($key+1),
											'lindex' => $extras_folder['index'],
											'contents'=>$extra['downloadUrl']['download']);
								array_push($extras_content, $asset);
							}
						}
						$extras_folder['contents'] = $extras_content;
						if($actual_count > 0)
						{
							array_push($chapter_contents[$current_chapter], $extras_folder);
						}
					}
					
				}
			}

		}
		$final_data['contents'][$current_chapter+$offset-1]['contents'] = $chapter_contents[$current_chapter];
		return $final_data;
	}

	function safe_filename($string)
	{
		return preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $string);
	}

	function get_links($type, $link)
	{
		if($type=='video')
		{
			$file = file_get_contents($link);
			preg_match('/<a href="(.*?)" id="download-video"/',$file,$matches);
			return $matches[1];
		}
		else if($type=="videomashup")
		{
			$file = file_get_contents($link);
		    preg_match('/"file":"(.*?)","label":"/',$file,$matches);
		    $matches[1] = str_replace('\\', '', $matches[1]);
			return $matches[1];
		}
	}

	function convert_to_filename ($string) {
		// Replace spaces with underscores and makes the string lowercase
		$string = str_replace (" ", "_", $string);
		$string = str_replace ("..", ".", $string);

		// Match any character that is not in our whitelist
		preg_match_all ("/[^0-9^A-Z^a-z^_^.]/", $string, $matches);

		// Loop through the matches with foreach
		foreach ($matches[0] as $value) {
			$string = str_replace($value, "", $string);
		}
		return $string;
	}

	function download_folder($basedir, $folder, $start = 0, $end = null, $list = null, $downloader = "wget")
	{
		if(isset($folder["index"]))
			$dir = $basedir.DIRECTORY_SEPARATOR.$folder["index"]."_".$folder["title"];
		else
			$dir = $basedir.DIRECTORY_SEPARATOR.$folder["title"];
		
		if(!file_exists($dir))
			mkdir($dir);	
		
		foreach ($folder["contents"] as $content)
		{
			if($content["type"]=="folder")
			{
				download_folder($dir,$content,$start,$end,$list,$downloader);
			}
			elseif($content["type"]=="file")
			{
				$curr_index = ($content['lindex']) ? ($content['lindex']) : ($content['index']);
				if((!empty($list) && in_array($curr_index, $list)) || (empty($list) && ((empty($start) || $curr_index >= $start) && (empty($end) || $curr_index <= $end)))){
					$outputfile = $dir.DIRECTORY_SEPARATOR.convert_to_filename($content['index']."_".$content['title'].".".$content['extension']);
					if(!file_exists($outputfile)) {
						if($content["data"] == "link")
							switch ($downloader) {
								case 'idm':
									exec("idman /d \"".$content['contents']."\" /p \"".$dir."\" /a /n /f \"".convert_to_filename($content['index']."_".$content['title'].".".$content['extension'])."\" & ping 127.0.0.1 -n 2 > nul");
									break;
								default:
									exec("wget -O ".$outputfile." \"".$content['contents']."\"");
									break;
							}
						else if($content["data"] == "text")
							file_put_contents($outputfile, $content['contents']);
					}
				}
			}
		}
	}
	function print_help() {
		echo "Usage: php udemy-dl.php [OPTIONS]\n";
		echo "OPTIONS\n";
		echo "  -h, --help\t\tPrint this help text and exit\n";
		echo "  -i, --id ID\t\tCourse ID of the course to be downloaded\n";
		echo "  -u, --url URL\t\tURL of the course to be downloaded\n\t\t\t(Ingored if course ID is specified)\n";
		echo "  -s, --start INDEX\tStarts downloading directly from file #INDEX\n";
		echo "  -e, --end INDEX\tStops downloading after file #INDEX\n";
		echo "  -l, --list I1, I2...\tDownloads all files with indexes specified in the list\n";
		echo "  -f, --folder FOLDER\tSpecifies the folder where files will be downloaded.\n\t\t\tDefault folder is current working directory\n";
		echo "  -d, --downloader NAME\tSpecifies the download manager to be used.\n\t\t\tOptions: wget, idm\n\t\t\tDefault is wget\n";
		die();
	}

	function get_cli_option($options,$long,$short) {
		if (isset($options[$long]) || isset($options[$short])) {
			return isset($options[$long]) ? $options[$long] : $options[$short];
		}
		return null;
	}
?>