<?php

require_once "lib.php";
require "sqlmanager.php";
require "dbmanager.php";

class SessionManager {
    public $error_register;
    public $error_login;
    public $user_privilege;
    public $mandant;
    public $user_id;

    function __construct($_config, $_get, $_post) {

        $myDbManager = new DbManager($_config['db_srv'], $_config['db_name'], $_config['db_user'], $_config['db_pass']);
        $myDbManager -> opendbconnection();

        $mySQLManager = new SQLManager($myDbManager -> connection, $_config);

        $current_method = "";
        if(isset($_GET['method'])) {
            $current_method = $_GET['method'];
        }

        if("login" == $current_method) {
            $mydata = $mySQLManager -> validate_user($_post['email'], $_post['password']);

            if(false <> $mydata and 0 < count($mydata)) {
            
                setcookie('email', $mydata[0]['email'], time() + COOKIE_TIMEOUT);
                setcookie('user_id', $mydata[0]['id'], time() + COOKIE_TIMEOUT);
            
                header("Location: ./");
                exit();
            } 
        
            $this -> error_login = "email not registered";
        }

        if("open_mandant" == $current_method) {
            setcookie('mandant', $_get['mandant'], time() + COOKIE_TIMEOUT);
            setcookie('privilege', USER_ADMIN, time() + COOKIE_TIMEOUT);

            header("Location: ./");
            exit();
        }

        if("register" == $current_method) {
        
            $mydata = $mySQLManager -> register_user($_post['email'], $_post['password']);
        
            if(false === $mydata)
                $this -> error_register = "email already taken";
        }

        if("logout" == $current_method) {
            setcookie("email", "", time() - COOKIE_TIMEOUT);
            setcookie("privilege", "", time() - COOKIE_TIMEOUT);
            setcookie("mandant", "", time() - COOKIE_TIMEOUT);
            setcookie("user_id", "", time() - COOKIE_TIMEOUT);
        
            header("Location: ./");
        }

        $this -> user_privilege = isset($_COOKIE['privilege']) ? $_COOKIE['privilege'] : -1;
        $this -> mandant = isset($_COOKIE['mandant']) ? $_COOKIE['mandant'] : -1;
        $this -> user_id = isset($_COOKIE['user_id']) ? $_COOKIE['user_id'] : -1;
    }

    function logged_in() {
        if(0 <= $this -> user_id) {
            return true;
        }

        return false;
    }

}

?>