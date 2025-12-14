<table>
  <tr>
<?php

require "config.php";

$privilege = $_COOKIE['privilege'];
$i_am = basename($_SERVER['PHP_SELF']);

function print_menu_item($current, $target, $display) {
    $result = "<td";
    if($current == $target) $result .= " class=\"active\"";
    else $result .= " class=\"passive\"";
    $result .= "><a href=\"" . $target . "\">";
    $result .= $display;
    $result .= "</a></td>";

    echo $result;
}

print_menu_item($i_am, "index.php", "Home");

if(USER_WRITE <= $privilege) {
    print_menu_item($i_am, "add_entry.php", "Add Entry");
}

if(USER_POWER <= $privilege) {
    print_menu_item($i_am, "depot.php", "Depot");
}

if(USER_ADMIN <= $privilege) {
    print_menu_item($i_am, "attribute.php", "Attribute");
    print_menu_item($i_am, "mandant.php", "Mandant");
}

?>
  </tr>
</table>
  <br/><br/>
  Mandant: <?php echo $_COOKIE['mandant_name']; ?> | Userlevel: <?php echo $privilege; ?>