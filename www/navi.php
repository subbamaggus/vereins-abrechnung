<div class="w3-row">
<?php

require "config.php";

$privilege = $_COOKIE['privilege'];
$i_am = basename($_SERVER['PHP_SELF']);

function print_menu_item($current, $target, $display) {
    $result = "<a href=\"". $target . "\"><div class=\"w3-col tablink w3-bottombar w3-hover-light-grey w3-padding";
    if($current == $target) $result .= " w3-border-blue";
    $result .= "\" style=\"width:20%\">". $display ."</div>";
    $result .= "</a>";

    echo $result;
}

print_menu_item($i_am, "index.php", "Home");

if(USER_WRITE <= $privilege) {
    print_menu_item($i_am, "add_entry.php", "New");
}

if(USER_POWER <= $privilege) {
    print_menu_item($i_am, "depot.php", "Depot");
}

if(USER_ADMIN <= $privilege) {
    print_menu_item($i_am, "attribute.php", "Attribute");
}

if(GLOBAL_ADMIN <= $privilege) {
    print_menu_item($i_am, "mandant.php", "Mandant");
}

?>
</div>

  <br/><br/>
  Mandant: <?php echo $_COOKIE['mandant_name']; ?> | Userlevel: <?php echo $privilege; ?>