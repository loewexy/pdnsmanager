<?php

/* 
 * Copyright 2016 Lukas Metzger <developer@lukas-metzger.com>.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once '../config/config-default.php';
require_once '../lib/database.php';
require_once '../lib/session.php';

$input = json_decode(file_get_contents('php://input'));

if(!isset($input->csrfToken) || $input->csrfToken !== $_SESSION['csrfToken']) {
    echo "Permission denied!";
    exit();
}

if(!isset($_SESSION['type']) || $_SESSION['type'] != "admin") {
    echo "Permission denied!";
    exit();
}

if(isset($input->action) && $input->action == "getUsers") {

    $sql = "
        SELECT id,name,type
        FROM user
        WHERE
            (name LIKE ? OR ?) AND
            (type=? OR ?)
    ";

    if(isset($input->sort->field) && $input->sort->field != "") {
        if($input->sort->field == "id") {
            $sql .= "ORDER BY id";
        } else if($input->sort->field == "name") {
            $sql .= "ORDER BY name";
        } else if($input->sort->field == "type") {
            $sql .= "ORDER BY type";
        }

        if(isset($input->sort->order)) {
            if($input->sort->order == 0) {
                $sql .= " DESC";
            } else if($input->sort->order == 1) {
                $sql .= " ASC";
            }
        }
    }

    $stmt = $db->prepare($sql);

    if(isset($input->name)) {
        $name_filter = "%" . $input->name . "%";
        $name_filter_used = 0;
    } else {
        $name_filter = "";
        $name_filter_used = 1;
    }

    if(isset($input->type)) {
        $type_filter = $input->type;
        $type_filter_used = 0;
    } else {
        $type_filter = "";
        $type_filter_used = 1;
    }

    $stmt->bind_param("sisi",
            $name_filter, $name_filter_used,
            $type_filter, $type_filter_used
    );
    $stmt->execute();

    $result = $stmt->get_result();

    $retval = Array();

    while($obj = $result->fetch_object()) {
        $retval[] = $obj;
    }
}

if(isset($input->action) && $input->action == "deleteUser") {
    $userId = $input->id;
    
    $db->autocommit(false);
    
    $stmt = $db->prepare("DELETE FROM permissions WHERE user=?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
    
    $stmt = $db->prepare("DELETE FROM user WHERE id=?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
    
    $db->commit();
}

if(isset($retval)) {
    echo json_encode($retval);
} else {
    echo "{}";
}
