<?php

require "config.php";
require('lib.php');

$user_privilege = -1;
$login_error = "";
$register_error = "";

function is_method($get, $method) {
    if(isset($get['method']) and $method == $get['method']) {
        return true;
    }
    

    return false;  
}

if(isset($_GET['method']) and "login" == $_GET['method']) {
    session_start();

    $myDbManager = new DbManager($db_srv, $db_name, $db_user, $db_pass);
    $myDbManager -> opendbconnection();

    $mySQLManager = new SQLManager($myDbManager -> connection, 1);
    $mydata = $mySQLManager -> validate_user($_POST['email'], $_POST['password']);

    if(false <> $mydata and 0 < count($mydata)) {

        $user_privilege = $mydata[0]['privilege'];
      
        setcookie('email', $mydata[0]['email'], time() + 3600);
        setcookie('privilege', $mydata[0]['privilege'], time() + 3600);
      
        header("Location: ./");
        exit();
    } 

    $login_error = "email not registered";
}

if(isset($_GET['method']) and "register" == $_GET['method']) {
    session_start();

    $myDbManager = new DbManager($db_srv, $db_name, $db_user, $db_pass);
    $myDbManager -> opendbconnection();

    $mySQLManager = new SQLManager($myDbManager -> connection, 1);
    $mydata = $mySQLManager -> register_user($_POST['email'], $_POST['password']);

    if(false === $mydata)
        $register_error = "email already taken";
}

if(isset($_GET['method']) and "logout" == $_GET['method']) {
    setcookie("email", "", time() - 3600);
    setcookie("privilege", "", time() - 3600);

    header("Location: ./");
}

$user_privilege = isset($_COOKIE['privilege']) ? $_COOKIE['privilege'] : -1;

$myWebManager = new WebManager();

$myWebManager -> body_start();
$logged_in = 0 <= $user_privilege;


if(!$logged_in) { 
    $myWebManager -> login_form($login_error);
    $myWebManager -> register_form($register_error);
} else {
    $myWebManager -> menu();

    if(is_method($_GET, "add_entry")) {
        $myWebManager -> entry();
    } else {
        $myWebManager -> main();
    }
        
}

$myWebManager -> body_end();

$myWebManager -> render_page();

?>