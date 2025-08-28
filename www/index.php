<?php

require "config.php";

require "sessionmanager.php";
require "webmanager.php";


$login_error = "";
$register_error = "";

$mySessionManager = new SessionManager($config, $_GET, $_POST);

$myWebManager = new WebManager();


$myWebManager -> body_start();

if(!$mySessionManager -> logged_in()) { 
    $myWebManager -> login_form($mySessionManager -> error_login);
    $myWebManager -> register_form($mySessionManager -> error_register);
} else {
    if(is_method($_GET, "add_entry")) {
        $myWebManager -> entry();
    } else {
        $myWebManager -> menu();
        $myWebManager -> main();
    }
}

$myWebManager -> body_end();

$myWebManager -> render_page();

?>