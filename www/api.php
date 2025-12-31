<?php

require "config.php";
require_once "lib.php";
require "sessionmanager.php";

header('Content-Type: application/json');

try {
    require "api_helper.php";

    echo json_encode($mydata);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
