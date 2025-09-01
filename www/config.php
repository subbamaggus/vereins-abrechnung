<?PHP

	$config['db_srv'] = 'localhost';
	$config['db_name'] = 'accounting';
	$config['db_user'] = 'newuser';
	$config['db_pass'] = 'password';

	define('USER_REGISTERED', 0);
	define('USER_VALIDATED', 1);
	define('USER_WRITE', 2);
	define('USER_ADMIN', 3);

	define('COOKIE_TIMEOUT', 3600);
?>