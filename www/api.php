<?php

require "config.php";
require_once "lib.php";
require "sessionmanager.php";

header('Content-Type: application/json');

try {
    $myDbManager = new DbManager($config['db_srv'], $config['db_name'], $config['db_user'], $config['db_pass']);
    $myDbManager->opendbconnection();

    $mySQLManager = new SQLManager($myDbManager->connection, $config);

    if (is_method($_GET, "login")) {
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

    if (is_method($_GET, "register")) {
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

    if (is_method($_GET, "logout")) {
        setcookie("email", "", time() - COOKIE_TIMEOUT);
        setcookie("privilege", "", time() - COOKIE_TIMEOUT);
        setcookie("mandant", "", time() - COOKIE_TIMEOUT);
        setcookie("user_id", "", time() - COOKIE_TIMEOUT);
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

    if (is_method($_GET, "get_mandants")) {
        $mydata = $mySQLManager->get_mandants();
    } elseif (is_method($_GET, "get_years")) {
        $mydata = $mySQLManager->get_years();
    } elseif (is_method($_GET, "get_attributes")) {
        $mydata = $mySQLManager->get_attributes();
    } elseif (is_method($_GET, "get_items")) {
        $mydata = $mySQLManager->get_items();
    } elseif (is_method($_GET, "get_items_with_attributes")) {
        if (isset($_GET['attributes'])) {
            $mydata = $mySQLManager->get_items_with_attributes($_GET['attributes']);
        } else {
            $mydata = $mySQLManager->get_items_without_attributes();
        }
    } elseif (is_method($_GET, "store_entry")) {
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
    } elseif (is_method($_GET, "set_mandant")) {
        if (isset($_POST['mandant'])) {
            setcookie('mandant', $_POST['mandant'], time() + COOKIE_TIMEOUT);
            setcookie('privilege', USER_ADMIN, time() + COOKIE_TIMEOUT);
            $mydata = ['success' => true];
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Mandant ID not provided']);
            exit();
        }
    } elseif (is_method($_GET, "set_attribute")) {
        $mydata = $mySQLManager->set_attribute($_POST['item_id'], $_POST['attribute_id']);
    } elseif (is_method($_GET, "reset_attribute")) {
        $mydata = $mySQLManager->reset_attribute($_POST['item_id'], $_POST['attribute_id']);
    } elseif (is_method($_GET, "set_attributes_bulk")) {
        $post_data = json_decode(file_get_contents('php://input'), true);
        $mydata = $mySQLManager->set_attributes_bulk($post_data['item_ids'], $post_data['attribute_id']);
    } elseif (is_method($_GET, "reset_attributes_bulk")) {
        $post_data = json_decode(file_get_contents('php://input'), true);
        $mydata = $mySQLManager->reset_attributes_bulk($post_data['item_ids'], $post_data['attribute_id']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid method']);
        exit();
    }

    echo json_encode($mydata);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
