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