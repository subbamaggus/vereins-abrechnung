<?PHP

	$config['db_srv'] = 'localhost';
	$config['db_name'] = 'accounting';
	$config['db_user'] = 'newuser';
	$config['db_pass'] = 'password';
	$config['image_path'] = 'items/';

	define('USER_REGISTERED', 0);
	define('USER_VALIDATED', 1);
	define('USER_WRITE', 2);
	define('USER_POWER', 3);
	define('USER_ADMIN', 4);

	define('COOKIE_TIMEOUT', 36000);
?>