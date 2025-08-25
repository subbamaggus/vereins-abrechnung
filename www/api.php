<?php

require "config.php";
require "lib.php";

$myDbManager = new DbManager($db_srv, $db_name, $db_user, $db_pass);
$myDbManager -> opendbconnection();

$mySQLManager = new SQLManager($myDbManager -> connection, 1);

//$mySQLManager -> insert_item("zweite", "-100", "2025-08-23");

if(isset($_GET['method']) and "get_years" == $_GET['method']) {
    $mydata = $mySQLManager -> get_years();
}
else {
    $mydata = $mySQLManager -> get_items();
}


$mydata_json = json_encode($mydata);

echo $mydata_json;

?>