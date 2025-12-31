<?php

require "config.php";
require_once "lib.php";
require "sessionmanager.php";

try {
    require "api_helper.php";

    if("csv" == $_GET['mode']) {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=file.csv");
        
        //prepare for elster
        echo "#v2.4\r\n";

        echo json_encode($mydata);
        return;
    }

    if("pdf" == $_GET['mode']) {
        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=file.pdf");
        echo json_encode($mydata);
        return;
    }

    header('Content-Type: application/json');
    echo json_encode($mydata);
    return;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
