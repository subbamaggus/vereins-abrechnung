<?php

if(!isset($_COOKIE['mandant'])) {
  header("Location: index.php");
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="initial-scale=1, maximum-scale=1">
  <link href="main.css" rel="stylesheet">
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
  <title>Vereinsabrechnung - Manage Attributes</title>
  <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
</head>
<body>
  <?php include 'navi.php' ?>

  <div id="mandant-app"></div>
  <script src="mandant.js"></script>
  
</body>
</html>
