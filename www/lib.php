<?php

function value_if_isset($get, $name) {
    $value = null;
    if(isset($_GET[$name]))
        $value = $_GET[$name];

    return $value;
}

?>