<?php

require('config.php');

function int_to_eur($value) {
    $value = round($value / 100, 2);
    
    $value = number_format($value,2,'.','');
    $value = str_replace('.',',',$value);
    return $value;
}

function eur_to_int($value) {
    $value = str_replace('-','-',$value);
    
    //$value = str_replace('.','',$value);
    //$value = str_replace(',','.',$value);
    $value = number_format($value,2,'.','');

    $value = round($value * 100, 0);
    return $value;
}

$conn = connect($servername, $username, $password, $dbname) or die("Connection failed: " . connect_error());
if (connect_errno()) {
    printf("Connect failed: %s\n", connect_error());
    exit();
}

function connect($servername, $username, $password, $dbname) {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("latin1_swedish_ci");
    
    return $conn;
}

function connect_error() {

    return mysqli_connect_error();
}

function connect_errno() {

    return mysqli_connect_errno();
}

function query($conn, $sql) {

    return $conn->query($sql);
}

function error($conn) {

    return mysqli_error($conn);
}

function fetch_assoc($res) {

    return mysqli_fetch_assoc($res);
}

function copy_data($data) {
    $itemsByReference = array();

    foreach($data as $key => &$item) {
        $pos = strpos($key, 'data_');
        if($pos !== false and 0 == $pos) {
            //echo $key . "\n";
            $itemsByReference[$key] = null;
            if (isset($data[$key]))
                $itemsByReference[$key] = $data[$key];

        }
    }

    return $itemsByReference;
}

function get_data_with_sql($conn, $sql, $data) {
    $data = array();

    //$res = query($conn, $sql) or die("database error:". error($conn));
    $res = $conn->prepare($sql) or die("database error:". error($conn));

    $res->bind_param("i", $data);
    $res->execute();
    var_dump($res);

    while( $row = fetch_assoc($res) ) {
        $data[] = $row;
    }

    return $data;
}

function before_subtotal($event, $clean = "true") {

    $sql = "SELECT *
            FROM
                account_entry
            WHERE
                before_subtotal = 1";

    if ("true" == $clean)
        $sql .= " AND (cash = 0 or (cash = 1 and bill_available > 0))";

    if(NULL != $event)
        $sql .= " AND event = $event";

    return $sql;
}

function after_subtotal($event, $clean = "true") {

    $sql = "SELECT *
            FROM
                account_entry
            WHERE
                before_subtotal = 0";

    if ("true" == $clean)
        $sql .= " AND (cash = 0 or (cash = 1 and bill_available > 0))";

    if(NULL != $event)
        $sql .= " AND event = $event";

    return $sql;
}

function sql_store_entry($name, $date, $value, $event, $hidden) {
    $sql = "INSERT INTO `account_entry` (`value`, `name`, `date`) VALUES ('" . $value . "', '" . $name . "', '" . $date . "')";
    if(0 < $event)
        $sql = "INSERT INTO `account_entry` (`value`, `name`, `date`, `event`) VALUES ('" . $value . "', '" . $name . "', '" . $date . "', '" . $event . "')";
    
    return $sql;
}

?>