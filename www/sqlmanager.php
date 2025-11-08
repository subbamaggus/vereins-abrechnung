<?php

class SQLManager {
    public $connection;
    public $mandant;
    public $user_id;
    public $config;

    function __construct($_connection, $_config) {
        $this -> connection = $_connection;
        $this -> config = $_config;
    }

    function int2eur($value) {
        return number_format($value / 100, 2, '.', '');
    }

    function insert_item($_name, $_value, $_date) {
        $sql = "INSERT INTO account_item (name, value, date, user, mandant_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("sssii", $name, $value, $date, $user_id, $this -> mandant);

        $name = $_name;
        $value = $_value * 100;
        $date = $_date;
        $user_id = $this -> user_id;

        $stmt -> execute();

        $last_id = $stmt -> insert_id;

        return $last_id;
    }

    function update_image_name($_id, $_newname) {
        $sql = "UPDATE account_item SET file = ? WHERE id = ? and mandant_id = ?";
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("sii", $filename, $id, $this -> mandant);

        $filename = $_newname;
        $id = $_id;

        $stmt -> execute();

        return true;
    }

    function get_all_items() {
        $sql = "SELECT * FROM account_item WHERE mandant_id = ? ORDER BY date";
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("i", $this -> mandant);

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

    function get_items($_attributelist, $_year) {
        if(0 > $this -> mandant)
            throw new ErrorException("no mandant");

        $year = $_year;
        if(empty($year)) {
            $year = date("Y");
        }

        if(empty($_attributelist)) {
            $sql = <<<END
                SELECT *
                  FROM account_item
                 WHERE mandant_id = {$this -> mandant}
                   AND DATE_FORMAT(date, '%Y') = ($year)
                 ORDER BY date
            END;
            $stmt = $this->connection->prepare($sql);
        }
        else {
            $attribute_ids = array_map('intval', explode(',', $_attributelist));
            $placeholders = implode(',', array_fill(0, count($attribute_ids), '?'));
            $types = str_repeat('i', count($attribute_ids));

            $sql = <<<END
                SELECT DISTINCT ai.*
                  FROM account_item ai
                 INNER JOIN account_item_attribute_item aiai ON ai.id = aiai.item_id
                 WHERE aiai.attribute_item_id IN ($placeholders)
                   AND ai.mandant_id = {$this -> mandant}
                   AND DATE_FORMAT(ai.date, '%Y') = ($year)
                 ORDER BY ai.date
            END;
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param($types, ...$attribute_ids);
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
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("i", $this -> mandant);

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
           select max(mystart)/100 as start
                , max(myend)/100 as ende
                , mydate
             from (
                    select case when name = 'start' then value else null END as mystart
                         , case when name = 'ende' then value else null END as myend
                         , mydate from (
                                select 'ende' as name
                                     , value
                                     , DATE_FORMAT(date, '%Y') as mydate
                                  from account_depot_value
                                 where date = (SELECT max(date) max_date FROM `account_depot_value` WHERE DATE_FORMAT(date, '%Y') = '$year' and mandant_id = ?)
                                 union select 'start' as name
                                     , value, DATE_FORMAT(date, '%Y') as mydate
                                  from account_depot_value
                                 where date = (SELECT min(date) min_date FROM `account_depot_value` WHERE DATE_FORMAT(date, '%Y') = '$year' and mandant_id = ?)
                                 ) myalias
                   ) myalias2
             group by mydate;
           END;

        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("ii", $this -> mandant, $this -> mandant);

        $stmt -> execute();

        $result = $stmt -> get_result();

        $data = $result -> fetch_all(MYSQLI_ASSOC);

        return $data;
    }

    function get_mandants() {
        $sql = "SELECT mu.id as id, m.id as mid, m.name, mu.privilege FROM account_mandant m, account_mandant_user mu WHERE mu.mandant_id = m.id AND mu.user_id = ?";

        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("i", $user_id);

        $user_id = $this -> user_id;

        $stmt -> execute();

        $result = $stmt -> get_result();

        $data = $result -> fetch_all(MYSQLI_ASSOC);

        return $data;
    }

    function validate_user($email, $password) {
        $sql = "SELECT * FROM account_user where email = ?";
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
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("ss", $email, $pw_hash);

        $email = $email;
        $pw_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt -> execute();

            $result = $stmt -> get_result();

            error_log("insert result" . json_encode($result));

            $data = array( "success" => "done",);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $data = false;
        }

        return $data;
    }

    function get_attributes() {
        $sql = "SELECT * FROM account_attribute WHERE mandant_id = ?";
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("i", $this -> mandant);

        $stmt -> execute();

        $result = $stmt -> get_result();

        $attributes = $result -> fetch_all(MYSQLI_ASSOC);

        $sql = "SELECT * FROM account_attribute_item WHERE mandant_id = ?";
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("i", $this -> mandant);

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
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("iii", $item_id, $attribute_id, $this -> mandant);

        $stmt -> execute();

        $result = $stmt -> get_result();

        $count = $result -> num_rows;
        if(0 < $count) {
            return ['success' => true];
        }

        $sql = "INSERT INTO account_item_attribute_item (item_id, attribute_item_id, mandant_id) VALUES (?, ?, ?)";
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("iii", $item_id, $attribute_id, $this -> mandant);

        $stmt -> execute();

        return ['success' => true, 'inserted' => true];
    }

    function reset_attribute($item_id, $attribute_id) {
        $sql = "DELETE FROM account_item_attribute_item WHERE item_id = ? AND attribute_item_id = ? AND mandant_id = ?";
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("iii", $item_id, $attribute_id, $this -> mandant);

        $stmt -> execute();

        return ['success' => true];
    }

    function set_attributes_bulk($item_ids, $attribute_id) {
        $sql = "INSERT INTO account_item_attribute_item (item_id, attribute_item_id, mandant_id) VALUES (?, ?, ?)";

        $this -> connection -> begin_transaction();
        try {
            $stmt = $this -> connection -> prepare($sql);
            foreach ($item_ids as $item_id) {
                // Use IGNORE to prevent errors on duplicate entries
                $stmt -> bind_param("iii", $item_id, $attribute_id, $this -> mandant);
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

        $this -> connection -> begin_transaction();
        try {
            $stmt = $this -> connection -> prepare($sql);
            foreach ($item_ids as $item_id) {
                $stmt -> bind_param("iii", $item_id, $attribute_id, $this -> mandant);
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

}

?>
