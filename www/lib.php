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


class WebManager {
    public $content = "";
    
    function __construct() {
        $this -> content = <<<END
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="initial-scale=1, maximum-scale=1">
  <link href="main.css" rel="stylesheet">
  <title>Vereinsabrechnung</title>
  <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
</head>
END;
    }
    function body_start() {
        $this -> content .= <<<END

<body>
END;
    }
    function body_end() {
        $this -> content .= <<<END

</body>
END;
    }

    function render_page() {
        $this -> content .= <<<END

</html>
END;

        echo $this -> content;
    }

    function login_form($msg_error) {
        $this -> content .= <<<END

  LOGIN
  <form action="?method=login" method="post">
    email: <input type="text" name="email"/>$msg_error<br/>
    password: <input type="password" name="password"/><br/>
    <button type="submit" value="Submit">Submit</button>
  </form>
END;
    }

    function register_form($msg_error) {
        $this -> content .= <<<END

  REGISTER
  <form action="?method=register" method="post">
    email: <input type="text" name="email"/>$msg_error<br/>
    password: <input type="password" name="password"/><br/>
    <button type="submit" value="Submit">Submit</button>
  </form>
END;
    }

    function menu() {
        $this -> content .= <<<END

  <a href="?method=logout">logout</a> - 
  <a href="?method=add_entry">add entry</a>    
END;
    }

    function main() {
        $this -> content .= <<<END

  <div id="navi"></div>
  <script src="navi.js"></script>

  <div id="app"></div>
  <script src="app.js"></script>
END;
    }

    function entry() {
        $this -> content .= <<<EOD

<p>
<form enctype="multipart/form-data" id="input_entry" action="?" method="post" onsubmit="deactivateSubmitButton()">
    <label>mehrere Eintraege
        <input type="checkbox" id="multientry" name="multientry" value="checked" class="myinput"$multientry_checked>
    </label>
    <br>
    <br>
    <label>Betrag<br>
        <input type="number" step="0.01" id="value" name="value" class="myinput">
    </label>
    <br>
    <label>Datum<br>
        <input type="date" id="date" name="date" class="myinput">
    </label>
    <br>
    <label>Bezeichnung<br>
        <input type="text" id="name" name="name" data-clear-btn="true" class="myinput">
    </label>
    <br>
    <label>Kaffeekasse
        <input type="checkbox" id="hidden" name="hidden" value="checked" class="myinput">
    </label>
    <br><br>
    <label>Veranstaltung<br>
        <select name="event" id="event" class="myinput">
        $myevents
        </select>
    </label>
    <br>
    <label>Bild<br>
        <input type="file" accept="image/*" capture id="myimage" name="myimage" class="myinput">
    </label>
    <br>
    <input id="mysubmit" type="submit" value="speichern" class="myinput">
    <input type="hidden" value="store_entry" name="mode">
</form>
<form enctype="multipart/form-data" id="input_entry" action="?" method="post">
    <input type="submit" value="abbrechen" class="myinput">
</form>
</p>

EOD;
    }
}
?>