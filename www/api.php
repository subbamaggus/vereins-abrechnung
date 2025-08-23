<?php

require "config.php";

class DbManager {
    public $srv;
    public $name;
    public $user;
    public $pass;

    function __construct($_srv, $_name, $_user, $_pass) {
        $this->srv = $_srv;
        $this->name = $_name;
        $this->user = $_user;
        $this->pass = $_pass;
    }
}

$myDbManager = new DbManager($db_srv, $db_name, $db_user, $db_pass);

echo $myDbManager->pass;

?>