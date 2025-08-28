<?php

function is_method($get, $method) {
    if(isset($get['method']) and $method == $get['method']) {
        return true;
    }

    return false;  
}

?>