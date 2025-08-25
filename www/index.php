<?php
$user_privilege = -1;

if(isset($_GET['method']) and "login" == $_GET['method']) {
  require "config.php";
  require('lib.php');

  session_start();

  $myDbManager = new DbManager($db_srv, $db_name, $db_user, $db_pass);
  $myDbManager -> opendbconnection();

  $mySQLManager = new SQLManager($myDbManager -> connection, 1);
  $mydata = $mySQLManager -> validate_user($_POST['email'], $_POST['password']);

  if(0 < count($mydata)) {

    $user_privilege = $mydata[0]['privilege'];

    setcookie('email', $mydata[0]['email'], time() + 3600);
    setcookie('privilege', $mydata[0]['privilege'], time() + 3600);
  }
  
  header("Location: ./");
}

if(isset($_GET['method']) and "logout" == $_GET['method']) {
  error_log("logging out");
  setcookie("email", "", time() - 3600);
  setcookie("privilege", "", time() - 3600);

  header("Location: ./");
}

$user_privilege = isset($_COOKIE['privilege']) ? $_COOKIE['privilege'] : -1;

?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vereinsabrechnung</title>
  <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
</head>
<body>

<?php if(0 > $user_privilege) { ?>
  <form action="?method=login" method="post">
    email: <input type="text" name="email"/><br/>
    password: <input type="password" name="password"/><br/>
    <button type="submit" value="Submit">Submit</button>
  </form>
<?php } else { ?>
  <a href="?method=logout">logout</a>
<?php } ?>

<?php if(0 <= $user_privilege) { ?>
  <div id="navi"></div>
  <script src="navi.js"></script>

  <div id="app"></div>
  <script src="app.js"></script>
<?php } ?>

</body>
</html>
