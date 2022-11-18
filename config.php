<?php 

	header('Access-Control-Allow-Origin: *');
	// header('Content-type: application/json');

	// session_start();
	date_default_timezone_set("America/Sao_Paulo");

	define('INCLUDE_PATH','http://molian.com.br/olympo/');
	// define('INCLUDE_PATH','http://localhost/Olympo%20Tournaments/');
	
	$autoload = function($class) {
		include(__DIR__.'/class/'.$class.'.php');
	};

	spl_autoload_register($autoload);

	#get config
	$file = 'config.json';
	$handle = fopen($file, 'r');
	$read = fread($handle, filesize($file));
	fclose($handle);
	$config = (array)json_decode($read);
	$db = (array)$config['database'];

	define('HOST', $db['host']);
	define('DATABASE', $db['database']);
	define('USER', $db['user']);
	define('PASS', $db['pass']);
	
	define('NOME_EMPRESA','Olympo Tournaments');

?>