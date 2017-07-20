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
        FROM users
        WHERE
            (name LIKE :name1 OR :name2) AND
            (type=:type1 OR :type2)
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
    $stmt->bindValue(':name1', $name_filter, PDO::PARAM_STR);
    $stmt->bindValue(':name2', $name_filter_used, PDO::PARAM_INT);
    $stmt->bindValue(':type1', $type_filter, PDO::PARAM_INT);
    $stmt->bindValue(':type2', $type_filter_used, PDO::PARAM_INT);
    $stmt->execute();
    $retval = Array();
    while($obj = $stmt->fetchObject()) {
        $retval[] = $obj;
    }
}
if(isset($input->action) && $input->action == "deleteUser") {
    $userId = $input->id;
    $db->beginTransaction();
    $stmt = $db->prepare("DELETE FROM permissions WHERE userid=:userid");
    $stmt->bindValue(':userid', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $stmt = $db->prepare("DELETE FROM users WHERE id=:id");
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $db->commit();
}
if(isset($retval)) {
    echo json_encode($retval);
} else {
    echo "{}";
}
