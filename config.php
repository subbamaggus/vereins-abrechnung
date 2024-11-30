<?PHP

$servername = "localhost";
$username = "newuser";
$password = "password";
$dbname = "accounting";

if ("localhost" != $_SERVER["SERVER_NAME"]) {
	$servername = '';
	$dbname = '';
	$username = '';
	$password = '';
}

?>