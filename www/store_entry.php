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

$msg = "";
$imageFileType = strtolower(pathinfo(basename($_FILES["myimage"]["name"]),PATHINFO_EXTENSION));

$uploadOk = true;

if(true == $uploadOk) {
    $last_id = $mySQLManager -> insert_item($_POST['name'], $_POST['value'], $_POST['date']);
    
    $target_dir = "items/";
    $target_file = $target_dir . $last_id . "." . $imageFileType;

    move_uploaded_file($_FILES["myimage"]["tmp_name"], $target_file);

    $mySQLManager -> update_image_name($last_id, $target_file);

    $msg .= "New record created successfully. Last inserted ID is: " . $last_id;
}

if (isset($_POST['multientry']) and "true" == $_POST['multientry']) {
    header("Location: ./?method=add_entry&multientry=true");
    exit();
}

header("Location: ./");

?>