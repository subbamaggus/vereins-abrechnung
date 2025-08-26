<?php

require "config.php";
require "lib.php";

$myDbManager = new DbManager($db_srv, $db_name, $db_user, $db_pass);
$myDbManager -> opendbconnection();

$mySQLManager = new SQLManager($myDbManager -> connection, 1);

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