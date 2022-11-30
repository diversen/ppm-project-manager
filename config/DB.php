<?php
	
return [
	'url' => 'mysql:host=127.0.0.1;dbname=ppm',
	'username' => 'root',
	'password' => 'password',
	'options' => [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_STRINGIFY_FETCHES => false,
	],
];
