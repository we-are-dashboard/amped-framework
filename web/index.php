<?php
define('ROOT_PATH',dirname(dirname(__FILE__)));

if(strpos($_SERVER['HTTP_HOST'], 'api') === false)
	require_once('../application/mvc/service/Service.php');
else
	require_once('../application/api/service/Service.php');
	
Service::run();
