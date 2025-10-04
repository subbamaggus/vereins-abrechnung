<?php

require "config.php";
require_once "lib.php";
require "sessionmanager.php";

$myDbManager = new DbManager($config['db_srv'], $config['db_name'], $config['db_user'], $config['db_pass']);
$myDbManager -> opendbconnection();

$mySessionManager = new SessionManager($config, $_GET, $_POST);

$mySQLManager = new SQLManager($myDbManager -> connection);
$mySQLManager -> mandant = $mySessionManager -> mandant;
$mySQLManager -> user_id = $mySessionManager -> user_id;

if ($mySessionManager -> user_id < 0) {
    echo "no session";
    exit();
}

if(is_method($_GET, "get_mandants")) {
    $mydata = $mySQLManager -> get_mandants();
}

if(is_method($_GET, "get_years")) {
    $mydata = $mySQLManager -> get_years();
}

if(is_method($_GET, "get_items")) {
    $mydata = $mySQLManager -> get_items();
}

if(is_method($_GET, "get_items_with_attributes")) {
    error_log("test");

    if(isset($_GET['attributes'])) {
        error_log("test1");
        $mydata = $mySQLManager -> get_items_with_attributes2($_GET['attributes']);
    }
    else {
        error_log("test2");
        $mydata = $mySQLManager -> get_items_with_attributes();
    }    
}

$mydata_json = json_encode($mydata);

echo $mydata_json;

?>