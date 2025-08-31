<?php

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
  <script src="./general.js"></script>
</head>
END;
    }
    function body_start() {
        $this -> content .= <<<END

<body onload="initform()">
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

    function open_mandant() {
        $this -> content .= <<<END

  LOGIN
  
  <div id="mandant"></div>
  <script src="mandant.js"></script>

  <form action="?method=create_mandant" method="get">
    <input type="hidden" name="mandant"/>create mandant<br/>
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

    function entry($_multientry) {
        $multientry_checked = "";
        if("true" == $_multientry) {
            $mode = "add_entry";
            $multientry_checked = " checked";
        }

        $this -> content .= <<<EOD

<p>
<form enctype="multipart/form-data" id="input_entry" action="store_entry.php" method="post">
    <label>mehrere Eintraege
        <input type="checkbox" id="multientry" name="multientry" value="true" class="myinput"$multientry_checked>
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
    <label>Bild<br>
        <input type="file" accept="image/*" capture id="myimage" name="myimage" class="myinput">
    </label>
    <br>
    <input id="mysubmit" type="submit" value="speichern" class="myinput">
    <input type="hidden" value="store_entry" name="method">
</form>
<form enctype="multipart/form-data" id="input_entry" action="?" method="post">
    <input type="submit" value="abbrechen" class="myinput">
</form>
</p>

EOD;
    }
}

?>