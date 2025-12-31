<?PHP

	$config['db_srv'] = 'localhost';
	$config['db_name'] = 'accounting';
	$config['db_user'] = 'newuser';
	$config['db_pass'] = 'password';
	$config['image_path'] = 'items/';

	// user invited / registered online
	// no read access
	define('USER_REGISTERED', 0);

	// user validated
	// read access
	define('USER_VALIDATED', 1);

	// write access, add entries, nothing more
	define('USER_WRITE', 2);

	// manage everything for the mandant
	define('USER_POWER', 3);

	// add users to mandant
	define('USER_ADMIN', 4);

	// add mandant
	define('GLOBAL_ADMIN', 5);

	define('COOKIE_TIMEOUT', 36000);
?>