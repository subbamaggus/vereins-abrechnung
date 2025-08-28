<?php

require('sqlmanager.php');
require('dbmanager.php');

class SessionManager {
    public $error_register;
    public $error_login;
    public $user_privilege;
    public $mandant;

    function __construct($_config, $_get, $_post) {

        $myDbManager = new DbManager($_config['db_srv'], $_config['db_name'], $_config['db_user'], $_config['db_pass']);
        $myDbManager -> opendbconnection();

        $mySQLManager = new SQLManager($myDbManager -> connection);

        if(is_method($_get, "login")) {
            $mydata = $mySQLManager -> validate_user($_post['email'], $_post['password']);

            if(false <> $mydata and 0 < count($mydata)) {
            
                $this -> user_privilege = $mydata[0]['privilege'];
            
                setcookie('email', $mydata[0]['email'], time() + 3600);
                setcookie('privilege', $mydata[0]['privilege'], time() + 3600);
                setcookie('mandant', $mydata[0]['mandant'], time() + 3600);                
            
                header("Location: ./");
                exit();
            } 
        
            $this -> error_login = "email not registered";
        }

        if(is_method($_get, "register")) {
        
            $mydata = $mySQLManager -> register_user($_post['email'], $_post['password']);
        
            if(false === $mydata)
                $this -> error_register = "email already taken";
        }

        if(is_method($_get, "logout")) {
            setcookie("email", "", time() - 3600);
            setcookie("privilege", "", time() - 3600);
            setcookie("mandant", "", time() - 3600);
        
            header("Location: ./");
        }

        $this -> user_privilege = isset($_COOKIE['privilege']) ? $_COOKIE['privilege'] : -1;
        $this -> mandant = isset($_COOKIE['mandant']) ? $_COOKIE['mandant'] : -1;
    }

    function logged_in() {
        if(0 <= $this -> user_privilege) {
            return true;
        }

        return false;
    }

}

?>