<?php

require "config.php";

$privilege = $_COOKIE['privilege'];

?>

  <a href="index.php">Home</a>&nbsp;

<?php if(USER_WRITE <= $privilege) {

?>
  <a href="add_entry.php">Add Entry</a>&nbsp;
<?php  
}

?>
<?php if(USER_POWER <= $privilege) {

?>
  <a href="depot.php">Depot</a>&nbsp;
<?php  
}

?>
<?php if(USER_ADMIN <= $privilege) {

?>
  <a href="attribute.php">Attribute</a>&nbsp;
  <a href="mandant.php">Mandant</a>&nbsp;
<?php  
}

?>
  
  
  <br/><br/>
  Mandant: <?php echo $_COOKIE['mandant_name']; ?> | Userlevel: <?php echo $privilege; ?> 