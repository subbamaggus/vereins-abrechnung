<?php

require "config.php";

class DbManager {
    public $srv;
    public $name;
    public $user;
    public $pass;

    public $connection;

    function __construct($_srv, $_name, $_user, $_pass) {
        $this -> srv = $_srv;
        $this -> name = $_name;
        $this -> user = $_user;
        $this -> pass = $_pass;
    }

    function opendbconnection() {
        $this -> connection = new mysqli($this -> srv, $this -> user, $this -> pass, $this -> name) or die("Connection failed: " . connect_error());
        if ($this -> connection -> connect_error) {
            exit();
        }
    }
}

class SQLManager {
    public $connection;
    public $_mandant;

    function __construct($_connection, $_mandant) {
        $this -> connection = $_connection;
        $this -> mandant = $_mandant;
    }

    function insert_item($_name, $_value, $_date) {
        $sql = "INSERT INTO " . $this -> mandant . "_account_item (name, value, date) VALUES (?, ?, ?)";
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("sss", $name, $value, $date);

        $name = $_name;
        $value = $_value;
        $date = $_date;

        $stmt -> execute();
    }

    function get_items() {
        $sql = "SELECT * FROM " . $this -> mandant . "_account_item WHERE name=?";
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("s", $name);

        $name = "zweite";

        $stmt -> execute();

        $result = $stmt -> get_result();

        $data = $result -> fetch_all(MYSQLI_ASSOC);

        return $data;
    }

    function get_years() {
        $sql = "SELECT distinct DATE_FORMAT(date, '%Y') as year FROM " . $this -> mandant . "_account_item";
        $stmt = $this -> connection -> prepare($sql);

        $stmt -> execute();

        $result = $stmt -> get_result();

        $data = $result -> fetch_all(MYSQLI_ASSOC);

        return $data;
    }
}

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