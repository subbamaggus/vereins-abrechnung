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

$mySQLManager -> insert_item($_POST['name'], $_POST['value'], $_POST['date']);

//$msg = "";
//$imageFileType = strtolower(pathinfo(basename($_FILES["myimage"]["name"]),PATHINFO_EXTENSION));
//
//$uploadOk = false;
//
//$msg .= "File is not an image.";
//if($check !== false) {
//    $msg = "File is an image - " . $imageFileType . ".";
//    $uploadOk = true;
//    $msg .= $result;
//}
//
//if(true == $uploadOk) {
//    $sql = sql_store_entry($name, $date, eur_to_int($value), $event, $hidden);
//    $result = query($conn, $sql);
//    if ($result === TRUE) {
//        $last_id = $conn->insert_id;
//        
//        $target_dir = "uploads/";
//        $target_file = $target_dir . $last_id . "." . $imageFileType;
//        
//        move_uploaded_file($_FILES["myimage"]["tmp_name"], $target_file);
//        $msg .= "New record created successfully. Last inserted ID is: " . $last_id;
//    } else {
//        $msg .= "Error: " . $sql . "<br>" . $conn->error;
//    }
//}
//else
//    $msg .= "upload not ok.";

if (isset($_POST['multientry']) and "true" == $_POST['multientry']) {
    header("Location: ./?method=add_entry&multientry=true");
    exit();
}

header("Location: ./");

?>