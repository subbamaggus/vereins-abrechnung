<?php

class SQLManager {
    public $connection;
    public $mandant;
    public $user_id;

    function __construct($_connection) {
        $this -> connection = $_connection;
        
    }
    
    function int2eur($value) {
        return number_format($value / 100, 2, '.', '');
    }

    function insert_item($_name, $_value, $_date) {
        $sql = "INSERT INTO " . $this -> mandant . "_account_item (name, value, date, user) VALUES (?, ?, ?, ?)";
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("sssi", $name, $value, $date, $user_id);

        $name = $_name;
        $value = $_value * 100;
        $date = $_date;
        $user_id = $this -> user_id;

        $stmt -> execute();

        $last_id = $stmt -> insert_id;

        return $last_id;
    }

    function update_image_name($_id, $_newname) {
        $sql = "UPDATE " . $this -> mandant . "_account_item SET file = ? WHERE id = ?";
        $stmt = $this -> connection -> prepare($sql);
        $stmt -> bind_param("si", $filename, $id);

        $filename = $_newname;
        $id = $_id;

        $stmt -> execute();

        return true;        
    }

    function get_items() {
        $sql = "SELECT * FROM " . $this -> mandant . "_account_item";
        $stmt = $this -> connection -> prepare($sql);

        $stmt -> execute();

        $result = $stmt -> get_result();

        $data = $result -> fetch_all(MYSQLI_ASSOC);

        foreach ($data as &$single) {
            $single['value'] = $this -> int2eur($single['value']);
        }
        unset($single);

        return $data;
    }

    function get_items_with_attributes() {
        $data = $this -> get_items();

        $sql = <<<END
            SELECT ai.id, ai.file, a.id as a_id, a.name as a_name, aai.id as aai_id, aai.name as aai_name
              FROM {$this -> mandant}_account_item ai
                 , {$this -> mandant}_account_attribute_item aai
                 , {$this -> mandant}_account_item_attribute_item aiai
                 , {$this -> mandant}_account_attribute a 
             WHERE ai.id = aiai.item_id 
               AND aiai.attribute_item_id = aai.id 
               AND aai.attribute_id = a.id
             ORDER BY ai.id
            END;
        $stmt = $this -> connection -> prepare($sql);

        $stmt -> execute();

        $result = $stmt -> get_result();

        $data_with_attributes = $result -> fetch_all(MYSQLI_ASSOC);

        foreach ($data as &$single) {
            foreach($data_with_attributes as $attribute) {
                if($attribute['id'] == $single['id']) {
                    $single['attribute'][] = $attribute;
                }
            }
        }
        unset($single);

        return $data;
    }

    function get_items_with_attributes2($_attributelist) {
        $data = $this -> get_items();

        $sql = <<<END
            SELECT ai.id, ai.file, a.id as a_id, a.name as a_name, aai.id as aai_id, aai.name as aai_name
              FROM {$this -> mandant}_account_item ai
                 , {$this -> mandant}_account_attribute_item aai
                 , {$this -> mandant}_account_item_attribute_item aiai
                 , {$this -> mandant}_account_attribute a 
             WHERE ai.id = aiai.item_id 
               AND aiai.attribute_item_id = aai.id 
               AND aai.attribute_id = a.id
               AND aai.id in (8,9)
             ORDER BY ai.id
            END;
        $stmt = $this -> connection -> prepare($sql);
        //$stmt -> bind_param("s", $attributelist);
        $attributelist = $_attributelist;

        error_log($_attributelist);

        $stmt -> execute();

        $result = $stmt -> get_result();

        $data_with_attributes = $result -> fetch_all(MYSQLI_ASSOC);

        foreach ($data as &$single) {
            foreach($data_with_attributes as $attribute) {
                if($attribute['id'] == $single['id']) {
                    $single['attribute'][] = $attribute;
                }
            }
        }
        unset($single);

        return $data;
    }

    function get_years() {
        $sql = "SELECT distinct DATE_FORMAT(date, '%Y') as year FROM " . $this -> mandant . "_account_item ORDER BY 1 desc";
        $stmt = $this -> connection -> prepare($sql);

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
        $sql = "SELECT * FROM " . $this -> mandant . "_account_attribute";
        $stmt = $this -> connection -> prepare($sql);

        $stmt -> execute();

        $result = $stmt -> get_result();

        $attributes = $result -> fetch_all(MYSQLI_ASSOC);

        $sql = "SELECT * FROM " . $this -> mandant . "_account_attribute_item";
        $stmt = $this -> connection -> prepare($sql);

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

}

?>
