<?php

require "config.php";
require_once "lib.php";
require "sessionmanager.php";

$myDbManager = new DbManager($config['db_srv'], $config['db_name'], $config['db_user'], $config['db_pass']);
$myDbManager -> opendbconnection();

$mySQLManager = new SQLManager($myDbManager -> connection);
$mySQLManager -> set_mandant(1);

//$mySQLManager -> insert_item("zweite", "-100", "2025-08-23");

if(is_method($_GET, "get_years")) {
    $mydata = $mySQLManager -> get_years();
}

if(is_method($_GET, "get_items")) {
    $mydata = $mySQLManager -> get_items();
}

$mydata_json = json_encode($mydata);

echo $mydata_json;

?>