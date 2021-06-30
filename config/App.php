<?php

return [
	'server_scheme' => 'http',
	'server_name' => 'localhost:8000', // 'hostname -I' . ':8080', // 
	'site_name' => 'PPM',
	'timezone' => 'Europe/Copenhagen',
	'login_redirect' => '/overview',
	'logout_redirect' => '/account',
	'env' => 'dev',
	'base_path' => dirname(__FILE__) . '/..',
	'server_url' => 'http://localhost:8000',	
];
