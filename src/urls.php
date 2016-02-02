<?php
	$mobile_headers = [
					'X-Udemy-Client-Id: ad12eca9cbe17afac6259fe5d98471a6',
					'X-Mobile-Visit-Enabled: true',
					'X-Mobile-Client-Id: QzB6RUUJRkI6NzA63U16QkM=',
					'X-Version-Name: 2.3',
					'X-Client-Name: Udemy-Android',
					'Host: www.udemy.com',
					'Accept: application/json, text/plain, */*',
					'User-Agent: okhttp/2.3.0 UdemyAndroid 2.3(84) (phone)'
				];

	$urls = [
		'coursePage' => [
			'method' => 'GET',
			'options' => [
				'redirect' => true,
				'headers' => [
					'Referer: https://www.udemy.com/',
					'Host: www.udemy.com'
				]
			]
		],
		'register' => [
			'link' => 'https://www.udemy.com/api-2.0/users/',
			'method' => 'POST',
			'options' => [
				'headers' => array_merge($mobile_headers,
							 ['X-Udemy-Client-Secret: a7c630646308824b2301fdb60ecfd8a0947e82d5',
							  'Authorization: Basic YWQxMmVjYTljYmUxN2FmYWM2MjU5ZmU1ZDk4NDcxYTY6YTdjNjMwNjQ2MzA4ODI0YjIzMDFmZGI2MGVjZmQ4YTA5NDdlODJkNQ=='])
			]
		],
		'login' => [
			'link' => 'https://www.udemy.com/join/login-popup/?displayType=ajax&display_type=popup&showSkipButton=1&returnUrlAfterLogin=https%3A%2F%2Fwww.udemy.com%2F&next=https%3A%2F%2Fwww.udemy.com%2F&locale=en_US',
			'method' => 'GET',
			'options' => [
				'cookie' => true,
				'redirect' => false,
				'headers' => [
					'Referer: https://www.udemy.com/join/login-popup/?displayType=ajax&display_type=popup&showSkipButton=1&returnUrlAfterLogin=https%3A%2F%2Fwww.udemy.com%2F&next=https%3A%2F%2Fwww.udemy.com%2F&locale=en_US',
					'Host: www.udemy.com'
				]
			]
		],
		'subscribePreview' => [
			'link' => 'https://www.udemy.com/course/preview-subscribe/?courseId=:courseId',
			'method' => 'GET',
			'options' => [
				'cookie' => true,
				'redirect' => false,
				'headers' => [
					'Host: www.udemy.com',
					'Accept: application/json, text/plain, */*',
				],
			],
		],
		'course' => [
			'link' => 'https://www.udemy.com/api-1.1/courses/:courseId/curriculum?fields[lecture]=title,asset,extras,assetType,lectureIndex,contextInfo,courseId,url,isFree,chapterIndex,sortOrder,hasCaption',
			'method' => 'GET',
			'options' =>[
				'cookie' => false,
				'redirect' => true,
				'headers' => $mobile_headers
			]
		]
	];
?>