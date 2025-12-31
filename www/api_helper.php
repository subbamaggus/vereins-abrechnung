<?php

    $myDbManager = new DbManager($config['db_srv'], $config['db_name'], $config['db_user'], $config['db_pass']);
    $myDbManager->opendbconnection();

    $mySQLManager = new SQLManager($myDbManager->connection, $config);
    
    $current_method = "";
    if(isset($_GET['method'])) {
        $current_method = $_GET['method'];
    }

    if ("login" == $current_method) {
        $mydata = $mySQLManager->validate_user($_POST['email'], $_POST['password']);

        if (false <> $mydata and 0 < count($mydata)) {
            setcookie('email', $mydata[0]['email'], time() + COOKIE_TIMEOUT);
            setcookie('user_id', $mydata[0]['id'], time() + COOKIE_TIMEOUT);
            echo json_encode(['success' => true]);
            exit();
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            exit();
        }
    }

    if ("register" == $current_method) {
        $mydata = $mySQLManager->register_user($_POST['email'], $_POST['password']);

        if (false === $mydata) {
            http_response_code(409);
            echo json_encode(['error' => 'Email already taken']);
            exit();
        } else {
            echo json_encode(['success' => true]);
            exit();
        }
    }

    if ("logout" == $current_method) {
        setcookie("email", "", time() - COOKIE_TIMEOUT);
        setcookie("privilege", "", time() - COOKIE_TIMEOUT);
        setcookie("mandant", "", time() - COOKIE_TIMEOUT);
        setcookie("user_id", "", time() - COOKIE_TIMEOUT);
        setcookie("mandant_name", "", time() - COOKIE_TIMEOUT);
        echo json_encode(['success' => true]);
        exit();
    }

    $mySessionManager = new SessionManager($config, $_GET, $_POST);

    if ($mySessionManager->user_id < 0) {
        http_response_code(401);
        echo json_encode(['error' => 'No session']);
        exit();
    }

    $mySQLManager->mandant = $mySessionManager->mandant;
    $mySQLManager->user_id = $mySessionManager->user_id;

    $mydata = null;

    if ("store_entry" == $current_method) {
        $last_id = $mySQLManager->insert_item($_POST['name'], $_POST['value'], $_POST['date']);

        if (isset($_FILES["myimage"]) && file_exists($_FILES["myimage"]["tmp_name"])) {
            $imageFileType = strtolower(pathinfo(basename($_FILES["myimage"]["name"]), PATHINFO_EXTENSION));
            $target_dir = $config['image_path'];
            $target_file = $last_id . "_" . uniqid() . "." . $imageFileType;

            if (move_uploaded_file($_FILES["myimage"]["tmp_name"], $target_dir . $target_file)) {
                $mySQLManager->update_image_name($last_id, $target_file);
            }
        }

        $mydata = ['success' => true, 'last_id' => $last_id];
    } elseif ("set_mandant" == $current_method) {
        if (isset($_POST['mandant'])) {
            setcookie('mandant', $_POST['mandant'], time() + COOKIE_TIMEOUT);

            $mandant_name = "";
            $tmp = $mySQLManager->get_mandant_by_id($_POST['mandant'], $mySQLManager->user_id);
            $mandant_name = $tmp[0]['name'];
            $privilege = $tmp[0]['privilege'];

            setcookie('mandant_name', $mandant_name, time() + COOKIE_TIMEOUT);
            setcookie('privilege', $privilege, time() + COOKIE_TIMEOUT);

            $mydata = ['success' => true];
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Mandant ID not provided']);
            exit();
        }
    } elseif ("get_items" == $current_method) {
        $mydata = $mySQLManager->get_items(value_if_isset($_GET, 'attributes'), value_if_isset($_GET, 'year'), value_if_isset($_GET, 'depots'));
    } elseif ("save_group" == $current_method) {
        $last_id = $mySQLManager->save_group($_POST['groupid'], $_POST['text']);
        $mydata = ['success' => true, 'last_id' => $last_id];
    } elseif ("save_attribute" == $current_method) {
        $last_id = $mySQLManager->save_attribute($_POST['groupid'], $_POST['attributeid'], $_POST['text']);
        $mydata = ['success' => true, 'last_id' => $last_id];
    } elseif ("save_mandant" == $current_method) {
        $last_id = $mySQLManager->save_mandant($_POST['mandantid'], $_POST['text']);
        $mydata = ['success' => true, 'last_id' => $last_id];
    } elseif ("save_user" == $current_method) {
        $last_id = $mySQLManager->save_user($_POST['mandantid'], $_POST['usermail'], $_POST['text']);
        $mydata = ['success' => true, 'last_id' => $last_id];
    } elseif ("save_depot" == $current_method) {
        $last_id = $mySQLManager->save_depot($_POST['depotid'], $_POST['text']);
        $mydata = ['success' => true, 'last_id' => $last_id];
    } elseif ("save_depot_value" == $current_method) {
        $last_id = $mySQLManager->save_depot_value($_POST['depotid'], $_POST['entrydate'], $_POST['entryvalue']);
        $mydata = ['success' => true, 'last_id' => $last_id];
    } elseif ("set_depot" == $current_method) {
        $mydata = $mySQLManager->set_depot($_POST['item_id'], $_POST['depot_id']);
    } elseif ("reset_depot" == $current_method) {
        $mydata = $mySQLManager->reset_depot($_POST['item_id'], $_POST['depot_id']);
    } elseif ("set_attribute" == $current_method) {
        $mydata = $mySQLManager->set_attribute($_POST['item_id'], $_POST['attribute_id']);
    } elseif ("reset_attribute" == $current_method) {
        $mydata = $mySQLManager->reset_attribute($_POST['item_id'], $_POST['attribute_id']);
    } elseif ("set_attributes_bulk" == $current_method) {
        $post_data = json_decode(file_get_contents('php://input'), true);
        $mydata = $mySQLManager->set_attributes_bulk($post_data['item_ids'], $post_data['attribute_id']);
    } elseif ("reset_attributes_bulk" == $current_method) {
        $post_data = json_decode(file_get_contents('php://input'), true);
        $mydata = $mySQLManager->reset_attributes_bulk($post_data['item_ids'], $post_data['attribute_id']);
    } elseif ("update_item" == $current_method) {
        $post_data = json_decode(file_get_contents('php://input'), true);
        $mydata = $mySQLManager->update_item($post_data['id'], $post_data['field'], $post_data['value']);
    } elseif ("delete_item" == $current_method) {
        $mydata = $mySQLManager->delete_item($_POST['item_id']);
    } elseif ("get_balance" == $current_method) {
        $mydata = $mySQLManager->get_balance($_GET);
    } elseif ("get_summary" == $current_method) {
        $mydata = $mySQLManager->get_summary(value_if_isset($_GET, 'year'));

    } elseif (method_exists($mySQLManager, $current_method)) {
        $mydata = $mySQLManager->{$current_method}();
        
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid method']);
        exit();
    }

?>
