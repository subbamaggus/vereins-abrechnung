<?php

function value_if_isset($get, $name) {
    $value = null;
    if(isset($get[$name]) and ("" <> $get[$name]))
        $value = $get[$name];

    return $value;
}

?>