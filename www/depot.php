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
  <title>Vereinsabrechnung - Manage Attributes</title>
  <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
</head>
<body>
  <?php include 'navi.php' ?>

  <div id="depot-app"></div>
  <script src="depot.js"></script>
  
</body>
</html>
