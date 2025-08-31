<?php

class SQLManager {
    public $connection;
    public $mandant;
    public $user_id;

    function __construct($_connection) {
        $this -> connection = $_connection;
        
    }
    
    function insert_item($_name, $_value, $_date) {
        $sql = "INSERT INTO " . $this -> mandant . "_account_item (name, value, date, user) VALUES (?, ?, ?, ?)";
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("sssi", $name, $value, $date, $user_id);

        $name = $_name;
        $value = $_value;
        $date = $_date;
        $user_id = $this -> user_id;

        $stmt -> execute();

        $last_id = $stmt -> insert_id;

        return $last_id;
    }

    function get_items() {
        $sql = "SELECT * FROM " . $this -> mandant . "_account_item";
        $stmt = $this -> connection -> prepare($sql);

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

    function get_mandants() {
        $sql = "SELECT mu.id as id, m.id as mid, m.name, mu.privilege FROM account_mandant m, account_mandant_user mu WHERE mu.mandant_id = m.id AND mu.user_id = ?";
        error_log($sql);
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("i", $user_id);

        $user_id = $this -> user_id;

        $stmt -> execute();

        $result = $stmt -> get_result();

        $data = $result -> fetch_all(MYSQLI_ASSOC);
        error_log(json_encode($data));
        return $data;
    }

    function validate_user($email, $password) {
        $sql = "SELECT * FROM account_user where email = ?";
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
        $sql = "INSERT INTO account_user (email, password) VALUES (?,?)";
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