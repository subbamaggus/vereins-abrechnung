<?php

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
        $sql = "SELECT distinct DATE_FORMAT(date, '%Y') as year FROM " . $this -> mandant . "_account_item ORDER BY 1 desc";
        $stmt = $this -> connection -> prepare($sql);

        $stmt -> execute();

        $result = $stmt -> get_result();

        $data = $result -> fetch_all(MYSQLI_ASSOC);

        return $data;
    }

    function validate_user($email, $password) {
        $sql = "SELECT * FROM " . $this -> mandant . "_account_user where email = ?";
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("s", $email);

        $email = $email;
        
        $stmt -> execute();

        $result = $stmt -> get_result();

        $data = $result -> fetch_all(MYSQLI_ASSOC);

        $verify = password_verify($password, $data[0]['password']);

        if(false === $verify)
            $data = false;

        return $data;        
    }

    function register_user($email, $password) {
        $sql = "INSERT INTO " . $this -> mandant . "_account_user (email, password) VALUES (?,?)";
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("ss", $email, $pw_hash);

        $email = $email;
        $pw_hash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt -> execute();

            $result = $stmt -> get_result();

            error_log("insert result" . json_encode($result));

            $data = array( "success" => "done",);            
        } catch (Exception $e) {
            error_log($e->getMessage());
            $data = false;
        }

        return $data;        
    }
}

?>