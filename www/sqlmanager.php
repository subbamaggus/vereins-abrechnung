<?php

class SQLManager {
    public $connection;
    public $mandant;
    public $user_id;
    public $config;

    public $debug = false;

    function __construct($_connection, $_config) {
        $this -> connection = $_connection;
        $this -> config = $_config;
    }

    function int2eur($value) {
        return number_format($value / 100, 2, '.', '');
    }

    function debug_log($line, $message) {
        if($this->debug)
            error_log($line . ": " . $message);
    }

    function audit_log($_sql, $_types, $_params) {
        $sql = "INSERT INTO account_audit (sql_text, data_text, mandant_id) VALUES (?, ?, ?)";
        
        $this->debug_log(__LINE__, $sql);

        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("ssi", $logsql, $data, $mandant);

        $logsql = $_sql;
        $data = $_types . ":" . implode(',', $_params);
        $mandant = $this->mandant;

        try {
            $stmt -> execute();

            $result = $stmt -> get_result();

            $this->debug_log(__LINE__, "insert result" . json_encode($result));

            $data = array( "success" => "done",);
        } catch (Exception $e) {
            $this->debug_log(__LINE__, $e->getMessage());
            $data = false;
        }

        return $data;        
    }

    function validate_user($email, $password) {
        $sql = "SELECT * FROM account_user where email = ?";

        $this->debug_log(__LINE__, $sql);

        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("s", $email);

        $email = $email;

        $stmt -> execute();

        $result = $stmt -> get_result();

        $data = $result -> fetch_all(MYSQLI_ASSOC);

        $verify = password_verify($password, $data[0]['password']);

        if(false === $verify)
            $data = false;

        return $data;
    }

    function register_user($email, $password) {
        $sql = "INSERT INTO account_user (email, password) VALUES (?,?)";
        
        $this->debug_log(__LINE__, $sql);

        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("ss", $email, $pw_hash);

        $email = $email;
        $pw_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt -> execute();

            $result = $stmt -> get_result();

            $this->debug_log(__LINE__, "insert result" . json_encode($result));

            $data = array( "success" => "done",);
        } catch (Exception $e) {
            $this->debug_log(__LINE__, $e->getMessage());
            $data = false;
        }

        return $data;
    }

    function insert_item($_name, $_value, $_date) {
        $sql = "INSERT INTO account_item (name, value, date, user, mandant_id) VALUES (?, ?, ?, ?, ?)";

        $this->debug_log(__LINE__, $sql);

        $stmt = $this -> connection -> prepare($sql);

        $item_types = "sssii";
        $item_params = array($_name, $_value * 100, $_date, $this->user_id, $this->mandant);

        $stmt -> bind_param($item_types, ...$item_params);
        $this->audit_log($sql, $item_types, $item_params);

        $stmt -> execute();

        $last_id = $stmt -> insert_id;

        return $last_id;
    }

    function update_image_name($_id, $_newname) {
        $sql = "UPDATE account_item SET file = ? WHERE id = ? and mandant_id = ?";

        $this->debug_log(__LINE__, $sql);

        $stmt = $this -> connection -> prepare($sql);

        $item_types = "sii";
        $item_params = array($_newname, $_id, $this->mandant);

        $stmt -> bind_param($item_types, ...$item_params);
        $this->audit_log($sql, $item_types, $item_params);

        $stmt -> execute();

        return true;
    }

    function update_item($id, $field, $value) {
        // Whitelist the field to prevent SQL injection
        $allowed_fields = ['date', 'name', 'value'];
        if (!in_array($field, $allowed_fields)) {
            throw new Exception("Invalid field specified for update.");
        }

        // Handle value conversion for the 'value' field
        if ($field === 'value') {
            $value = str_replace(',', '.', $value);
            $value = floatval($value) * 100;
            $param_type = "i"; // integer
        } else {
            $param_type = "s"; // string
        }

        $sql = "UPDATE account_item SET $field = ? WHERE id = ? AND mandant_id = ?";

        $this->debug_log(__LINE__, $sql);

        $stmt = $this->connection->prepare($sql);

        $item_types = $param_type . "ii";
        $item_params = array($value, $id, $this->mandant);

        $stmt -> bind_param($item_types, ...$item_params);
        $this->audit_log($sql, $item_types, $item_params);

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return ['success' => true];
        } else {
            // This could also mean the value was the same as before, which is not an error.
            return ['success' => true, 'message' => 'No rows updated.'];
        }
    }

    function delete_item($item_id) {
        $this->connection->begin_transaction();
        try {
            // First, delete associated attributes
            $sql_attributes = "DELETE FROM account_item_attribute_item WHERE item_id = ? AND mandant_id = ?";
            $stmt_attributes = $this->connection->prepare($sql_attributes);

            $item_types = "ii";
            $item_params = array($item_id, $this->mandant);

            $stmt_attributes -> bind_param($item_types, ...$item_params);
            $this->audit_log($sql_attributes, $item_types, $item_params);

            $stmt_attributes->execute();
            $stmt_attributes->close();

            // Then, delete the item itself
            $sql_item = "DELETE FROM account_item WHERE id = ? AND mandant_id = ?";
            $stmt_item = $this->connection->prepare($sql_item);

            $item_types = "ii";
            $item_params = array($item_id, $this->mandant);

            $stmt_item -> bind_param($item_types, ...$item_params);
            $this->audit_log($sql_item, $item_types, $item_params);

            $stmt_item->execute();
            
            if ($stmt_item->affected_rows > 0) {
                $this->connection->commit();
                return ['success' => true];
            } else {
                $this->connection->rollback();
                return ['success' => false, 'error' => 'Item not found or not authorized to delete.'];
            }
        } catch (Exception $e) {
            $this->connection->rollback();
            throw $e;
        }
    }

    function get_all_items() {
        $sql = "SELECT * FROM account_item WHERE mandant_id = ? ORDER BY date";

        $this->debug_log(__LINE__, $sql);

        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("i", $mandant);

        $mandant = $this->mandant;

        $stmt -> execute();

        $result = $stmt -> get_result();

        $data = $result -> fetch_all(MYSQLI_ASSOC);

        foreach ($data as &$single) {
            $single['value'] = $this -> int2eur($single['value']);

            if("" <> $single['file']) {
                $single['file'] = $this -> config['image_path'] . $single['file'];
            }
        }
        unset($single);

        return $data;
    }

    function get_items($_attributelist, $_year, $_depots) {
        if(0 > $this -> mandant)
            throw new ErrorException("no mandant");

        $year = $_year;
        if(empty($year)) {
            $year = date("Y");
        }
        $depot_sql = "";

        $base_sql = " FROM account_item ai WHERE ai.mandant_id = ? AND DATE_FORMAT(ai.date, '%Y') = (?)";
        $params = [$this->mandant, $year];
        $types = "is";

        if (!empty($_depots) or ("0" === $_depots)) {
            $depot_ids = array_map('intval', explode(',', $_depots));
            $placeholders = implode(',', array_fill(0, count($depot_ids), '?'));
            $depot_sql = " AND ai.depot_id IN ($placeholders)";
            $base_sql .= $depot_sql;
            $types .= str_repeat('i', count($depot_ids));
            $params = array_merge($params, $depot_ids);
        }

        if(empty($_attributelist)) {
            $sql = "SELECT *" . $base_sql . " ORDER BY date";
            $this->debug_log(__LINE__, $sql);
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param($types, ...$params);
        }
        else {
            $attribute_ids = array_map('intval', explode(',', $_attributelist));
            $attr_placeholders = implode(',', array_fill(0, count($attribute_ids), '?'));

            $sql = "SELECT DISTINCT ai.*
                      FROM account_item ai
                     INNER JOIN account_item_attribute_item aiai ON ai.id = aiai.item_id
                     WHERE ai.mandant_id = ? 
                       AND DATE_FORMAT(ai.date, '%Y') = (?)
                       $depot_sql
                       AND aiai.attribute_item_id IN ($attr_placeholders)
                     ORDER BY ai.date";

            $this->debug_log(__LINE__, $sql);
            $this->debug_log(__LINE__, $types . ":" . json_encode($params));

            $types = $types . str_repeat('i', count($attribute_ids));
            $params = array_merge($params, $attribute_ids);

            $stmt = $this->connection->prepare($sql);
            $this->debug_log(__LINE__, $types . ":" . json_encode($params));
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);

        foreach ($data as &$single) {
            $single['value'] = $this->int2eur($single['value']);
            if (!empty($single['file'])) {
                $single['file'] = $this -> config['image_path'] . $single['file'];
            }
        }
        unset($single);

        if (empty($data)) {
            return [];
        }

        $sql = <<<END
            SELECT ai.id, a.id as a_id, a.name as a_name, aai.id as aai_id, aai.name as aai_name
              FROM account_item ai
                 , account_attribute_item aai
                 , account_item_attribute_item aiai
                 , account_attribute a
             WHERE ai.id = aiai.item_id
               AND aiai.attribute_item_id = aai.id
               AND aai.attribute_id = a.id
               AND ai.mandant_id = {$this -> mandant}
               AND DATE_FORMAT(ai.date, '%Y') = ($year)
        END;

        if(!empty($_attributelist)) {
            $item_ids = array_column($data, 'id');
            $item_placeholders = implode(',', array_fill(0, count($item_ids), '?'));
            $item_types = str_repeat('i', count($item_ids));

            $sql .= " AND ai.id IN ($item_placeholders)";
            $sql .= " ORDER BY ai.date";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param($item_types, ...$item_ids);
        }
        else {
            $sql .= " ORDER BY ai.date";
            $stmt = $this->connection->prepare($sql);
        }

        $this->debug_log(__LINE__, $sql);

        $stmt->execute();

        $result_attrs = $stmt->get_result();

        $data_with_attributes = $result_attrs->fetch_all(MYSQLI_ASSOC);

        foreach ($data as &$single) {
            $single['attribute'] = [];
            foreach ($data_with_attributes as $attribute) {
                if ($attribute['id'] == $single['id']) {
                    $single['attribute'][] = $attribute;
                }
            }
        }
        unset($single);

        return $data;
    }

    function get_years() {
        $sql = "SELECT distinct DATE_FORMAT(date, '%Y') as year FROM account_item WHERE mandant_id = ? ORDER BY 1 desc";

        $this->debug_log(__LINE__, $sql);

        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("i", $mandant);

        $mandant = $this->mandant;

        $stmt -> execute();

        $result = $stmt -> get_result();

        $data = $result -> fetch_all(MYSQLI_ASSOC);

        return $data;
    }

    function save_attribute($_groupid, $_itemid, $_text) {

        if(-1 == $_itemid) {
            if(-1 == $_groupid) {
                $sql = "INSERT INTO account_attribute (name, mandant_id) VALUES (?, ?)";

                $this->debug_log(__LINE__, $sql);

                $stmt = $this -> connection -> prepare($sql);

                $item_types = "si";
                $item_params = array($_text, $this->mandant);

                $stmt -> bind_param($item_types, ...$item_params);
                $this->audit_log($sql, $item_types, $item_params);

                $stmt -> execute();

                $last_id = $stmt -> insert_id;

                return $last_id;
            } else {
                $sql = "INSERT INTO account_attribute_item (name, mandant_id, attribute_id) VALUES (?, ?, ?)";

                $this->debug_log(__LINE__, $sql);

                $stmt = $this -> connection -> prepare($sql);

                $item_types = "sii";
                $item_params = array($_text, $this->mandant, $_groupid);

                $stmt -> bind_param($item_types, ...$item_params);
                $this->audit_log($sql, $item_types, $item_params);

                $stmt -> execute();

                $last_id = $stmt -> insert_id;

                return $last_id;
            }
        }

        if("" == $_itemid) {
            $sql = "UPDATE account_attribute SET name = ? WHERE id = ? AND mandant_id = ?";

            $this->debug_log(__LINE__, $sql);

            $stmt = $this -> connection -> prepare($sql);

            $item_types = "sii";
            $item_params = array($_text, $_groupid, $this->mandant);

            $stmt -> bind_param($item_types, ...$item_params);
            $this->audit_log($sql, $item_types, $item_params);

            $stmt -> execute();

            return "ok";         
        }

        if(0 < $_itemid and 0 < $_groupid) {
            $sql = "UPDATE account_attribute_item SET name = ? WHERE id = ? AND attribute_id = ? AND mandant_id = ?";

            $this->debug_log(__LINE__, $sql);

            $stmt = $this -> connection -> prepare($sql);

            $item_types = "siii";
            $item_params = array($_text, $_itemid, $_groupid, $this->mandant);

            $stmt -> bind_param($item_types, ...$item_params);
            $this->audit_log($sql, $item_types, $item_params);

            $stmt -> execute();
            
            return "ok";
        }

        return "uiui";
    }

    function get_attributes() {
        $sql = "SELECT * FROM account_attribute WHERE mandant_id = ?";

        $this->debug_log(__LINE__, $sql);

        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("i", $mandant);

        $mandant = $this->mandant;

        $stmt -> execute();

        $result = $stmt -> get_result();

        $attributes = $result -> fetch_all(MYSQLI_ASSOC);

        
        $sql = "SELECT * FROM account_attribute_item WHERE mandant_id = ?";

        $this->debug_log(__LINE__, $sql);

        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("i", $mandant);

        $mandant = $this->mandant;

        $stmt -> execute();

        $result = $stmt -> get_result();

        $attribute_items = $result -> fetch_all(MYSQLI_ASSOC);

        foreach ($attributes as &$single) {
            foreach($attribute_items as $attribute) {
                if($attribute['attribute_id'] == $single['id']) {
                    $single['attribute'][] = $attribute;
                }
            }
        }
        unset($single);

        return $attributes;
    }

    function set_attribute($item_id, $attribute_id) {
        $sql = "SELECT * FROM account_item_attribute_item WHERE item_id = ? AND attribute_item_id = ? AND mandant_id = ?";
        
        $this->debug_log(__LINE__, $sql);

        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("iii", $item_id, $attribute_id, $mandant);

        $mandant = $this->mandant;

        $stmt -> execute();

        $result = $stmt -> get_result();

        $count = $result -> num_rows;
        if(0 < $count) {
            return ['success' => true];
        }

        $sql = "INSERT INTO account_item_attribute_item (item_id, attribute_item_id, mandant_id) VALUES (?, ?, ?)";

        $this->debug_log(__LINE__, $sql);

        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("iii", $item_id, $attribute_id, $this -> mandant);

        $stmt -> execute();

        return ['success' => true, 'inserted' => true];
    }

    function reset_attribute($item_id, $attribute_id) {
        $sql = "DELETE FROM account_item_attribute_item WHERE item_id = ? AND attribute_item_id = ? AND mandant_id = ?";

        $this->debug_log(__LINE__, $sql);

        $stmt = $this -> connection -> prepare($sql);

        $item_types = "iii";
        $item_params = array($item_id, $attribute_id, $this->mandant);

        $stmt -> bind_param($item_types, ...$item_params);
        $this->audit_log($sql, $item_types, $item_params);

        $stmt -> execute();

        return ['success' => true];
    }

    function set_depot($item_id, $depot_id) {
        $sql = "UPDATE account_item SET depot_id = ? WHERE id = ? AND mandant_id = ?";
        
        $this->debug_log(__LINE__, $sql);

        $stmt = $this -> connection -> prepare($sql);

        $item_types = "iii";
        $item_params = array($depot_id, $item_id, $this->mandant);

        $stmt -> bind_param($item_types, ...$item_params);
        $this->audit_log($sql, $item_types, $item_params);

        $stmt -> execute();

        $count = $stmt -> affected_rows;

        if(0 < $count) {
            return ['success' => true];
        }

        return ['success' => false];
    }

    function reset_depot($item_id, $depot_id) {
        return $this->set_depot($item_id, 0);
    }

    function set_attributes_bulk($item_ids, $attribute_id) {
        $sql = "INSERT INTO account_item_attribute_item (item_id, attribute_item_id, mandant_id) VALUES (?, ?, ?)";
        
        $this->debug_log(__LINE__, $sql);

        $this -> connection -> begin_transaction();
        try {
            $stmt = $this -> connection -> prepare($sql);
            foreach ($item_ids as $item_id) {
                // Use IGNORE to prevent errors on duplicate entries
                $item_types = "iii";
                $item_params = array($item_id, $attribute_id, $this->mandant);

                $stmt -> bind_param($item_types, ...$item_params);
                $this->audit_log($sql, $item_types, $item_params);

                $stmt -> execute();
            }
            $stmt -> close();
            $this -> connection -> commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this -> connection -> rollback();
            throw $e;
        }
    }

    function reset_attributes_bulk($item_ids, $attribute_id) {
        $sql = "DELETE FROM account_item_attribute_item WHERE item_id = ? AND attribute_item_id = ? AND mandant_id = ?";

        $this->debug_log(__LINE__, $sql);

        $this -> connection -> begin_transaction();
        try {
            $stmt = $this -> connection -> prepare($sql);
            foreach ($item_ids as $item_id) {

                $item_types = "iii";
                $item_params = array($item_id, $attribute_id, $this->mandant);

                $stmt -> bind_param($item_types, ...$item_params);
                $this->audit_log($sql, $item_types, $item_params);

                $stmt -> execute();
            }
            $stmt -> close();
            $this -> connection -> commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this -> connection -> rollback();
            throw $e;
        }
    }

    function get_depots() {
        $sql = "SELECT * FROM account_depot WHERE mandant_id = ?";
        
        $this->debug_log(__LINE__, $sql);
        
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("i", $mandant);

        $mandant = $this->mandant;

        $stmt -> execute();

        $result = $stmt -> get_result();

        $depots = $result -> fetch_all(MYSQLI_ASSOC);


        $sql = <<<END
            SELECT * 
              FROM account_depot_value dv
                 , account_depot d
             WHERE d.mandant_id = ? 
               AND d.id = dv.depot_id
             ORDER BY date;
        END;

        $this->debug_log(__LINE__, $sql);
        
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("i", $mandant);

        $mandant = $this->mandant;

        $stmt -> execute();

        $result = $stmt -> get_result();

        $depot_values = $result -> fetch_all(MYSQLI_ASSOC);

        foreach ($depots as &$single) {
            foreach($depot_values as $depot_value) {
                if($depot_value['depot_id'] == $single['id']) {
                    $depot_value['value'] = $this->int2eur($depot_value['value']);
                    $single['depot_value'][] = $depot_value;
                }
            }
        }
        unset($single);

        return $depots;
    }

    function save_depot($_depotid, $_depotname) {
        if(-1 == $_depotid) {
            $sql = "INSERT INTO account_depot (name, mandant_id) VALUES (?, ?)";

            $this->debug_log(__LINE__, $sql);

            $stmt = $this -> connection -> prepare($sql);

            $item_types = "si";
            $item_params = array($filename, $_depotname, $this->mandant);

            $stmt -> bind_param($item_types, ...$item_params);
            $this->audit_log($sql, $item_types, $item_params);

            $stmt -> execute();

            $last_id = $stmt -> insert_id;

            return $last_id;
        }

        if(0 < $_depotid) {
            $sql = "UPDATE account_depot SET name = ? WHERE id = ? AND mandant_id = ?";

            $this->debug_log(__LINE__, $sql);

            $stmt = $this -> connection -> prepare($sql);

            $item_types = "sii";
            $item_params = array($_depotname, $_depotid, $this->mandant);

            $stmt -> bind_param($item_types, ...$item_params);
            $this->audit_log($sql, $item_types, $item_params);

            $stmt -> execute();

            return "ok";         
        }
    }

    function save_depot_value($_depotid, $_entrydate, $_entryvalue) {

        if(0 < $_depotid) {
            $sql = "INSERT INTO account_depot_value (depot_id, value, date) VALUES (?, ?, ?)";

            $this->debug_log(__LINE__, $sql);

            $stmt = $this -> connection -> prepare($sql);

            $item_types = "iis";
            $item_params = array($_depotid, $_entryvalue * 100, $_entrydate);

            $stmt -> bind_param($item_types, ...$item_params);
            $this->audit_log($sql, $item_types, $item_params);

            $stmt -> execute();

            return "ok";         
        }
    }
    
    function get_mandants() {
        $sql = "SELECT DISTINCT m.id as mid, m.name FROM account_mandant m, account_mandant_user mu WHERE mu.mandant_id = m.id AND mu.user_id = ?";

        $this->debug_log(__LINE__, $sql);

        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("i", $userid);

        $userid = $this->user_id;

        $stmt -> execute();

        $result = $stmt -> get_result();

        $data = $result -> fetch_all(MYSQLI_ASSOC);

        
        $sql = "SELECT DISTINCT mu.*, u.email FROM account_mandant m, account_mandant_user mu, account_user u WHERE mu.mandant_id = m.id AND mu.user_id = u.id";

        $this->debug_log(__LINE__, $sql);

        $stmt = $this -> connection -> prepare($sql);

        $stmt -> execute();

        $result = $stmt -> get_result();

        $details = $result -> fetch_all(MYSQLI_ASSOC);

        foreach ($data as &$single) {
            foreach($details as $detail) {
                if($detail['mandant_id'] == $single['mid']) {
                    $single['user'][] = $detail;
                }
            }
        }
        unset($single);

        return $data;        
    }

    function get_mandant($_apikey) {
        $sql = "SELECT * FROM account_mandant where apikey = ?";

        $this->debug_log(__LINE__, $sql . $_apikey);

        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("s", $apikey);

        $apikey = $_apikey;

        $stmt -> execute();

        $result = $stmt -> get_result();

        $data = $result -> fetch_all(MYSQLI_ASSOC);

        return $data;        
    }

    function get_mandant_by_id($_mandant_id, $_user_id) {
        $sql = "SELECT m.name, mu.privilege, mu.*
                  FROM account_mandant_user mu
                    , account_mandant m
                    , account_user u
                WHERE mu.mandant_id = m.id
                  AND mu.user_id = u.id
                  AND m.id = ? 
                  AND u.id = ?";

        $this->debug_log(__LINE__, $sql . $_mandant_id);

        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("ii", $mandant_id, $user_id);

        $mandant_id = $_mandant_id;
        $user_id = $_user_id;

        $stmt -> execute();

        $result = $stmt -> get_result();

        $data = $result -> fetch_all(MYSQLI_ASSOC);

        return $data;        
    }

    function get_summary($_year) {
        $year = $_year;
        if(empty($year))
            $year = date("Y");

        $sql = <<<END
           SELECT dv.depot_id as id
                , DATE_FORMAT(dv.date, '%Y') as mydate
                , dv.date
                , dv.value
             FROM account_depot_value dv
                , account_depot d
            WHERE d.id = dv.depot_id
              AND d.mandant_id = ?
              AND DATE_FORMAT(dv.date, '%Y') = '$year'
              order by dv.depot_id, dv.date;
           END;

        $this->debug_log(__LINE__, $sql);

        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("i", $mandant);

        $mandant = $this->mandant;

        $stmt -> execute();

        $result = $stmt -> get_result();

        $intermediate_data = $result -> fetch_all(MYSQLI_ASSOC);

        foreach ($intermediate_data as $single) {
            $first = false;
            if(isset($localdata)) {
                if($localdata['id'] <> $single['id']) {
                    $first = true;
                }
            } else {
                $first = true;
            }

            if (true == $first) {
                if(isset($localdata['id'])) {
                    // not very first
                    $data[] = $localdata;
                }

                $localdata['start'] = $single['value'] / 100;
                $localdata['date_start'] = $single['date'];

                $localdata['id'] = $single['id'];
                $localdata['mydate'] = $single['mydate'];
            } else {
                $localdata['end'] = $single['value'] / 100;
                $localdata['date_end'] = $single['date'];
            }

        }
        if(isset($localdata))
            $data[] = $localdata;
        else
            $data[] = null;

        return $data;
    }

    function get_users() {
        $sql = "SELECT id, email FROM account_user";

        $this->debug_log(__LINE__, $sql);

        $stmt = $this -> connection -> prepare($sql);
        $stmt -> execute();

        $result = $stmt -> get_result();

        $data = $result -> fetch_all(MYSQLI_ASSOC);

        return $data;
    }

    function get_balance($_get) {
        $data['items'] = $this->get_items(value_if_isset($_get, 'attributes'), value_if_isset($_get, 'year'));

        $data['depots'] = $this->get_summary(value_if_isset($_get, 'year'));

        return $data;
    }

}

?>
