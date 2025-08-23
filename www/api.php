<?php

require "config.php";

class DbManager {
    public $srv;
    public $name;
    public $user;
    public $pass;

    public $connection;

    function __construct($_srv, $_name, $_user, $_pass) {
        $this->srv = $_srv;
        $this->name = $_name;
        $this->user = $_user;
        $this->pass = $_pass;
    }

    function opendbconnection() {
        $this->connection = new mysqli($this->srv, $this->user, $this->pass, $this->name) or die("Connection failed: " . connect_error());
        if ($this->connection->connect_error) {
            exit();
        }
    }
}


class SQLManager {
    function insert_item($_connection, $_name, $_value, $_date) {
        $stmt = $_connection -> prepare("INSERT INTO 1_account_item (name, value, date) VALUES (?, ?, ?)");
        $stmt -> bind_param("sss", $name, $value, $date);

        // set parameters and execute
        $name = $_name;
        $value = $_value;
        $date = $_date;
        $stmt -> execute();
    }
}

$myDbManager = new DbManager($db_srv, $db_name, $db_user, $db_pass);
$myDbManager->opendbconnection();

$mySQLManager = new SQLManager();
$mySQLManager->insert_item($myDbManager->connection, "zweite", "-100", "2025-08-23");

echo $myDbManager->pass;

?>