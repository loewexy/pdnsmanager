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

if(isset($input->action) && $input->action == "getDomains") {
    // Check if the requested page is a number
    if(!(isset($input->page) && is_int($input->page) && $input->page > 0)) {
        echo "Requested page must be a positive number!";
        exit();
    }
    
    // Here we get the number of matching records
    $sql = "
        SELECT COUNT(*) AS anzahl
        FROM domains D
        LEFT OUTER JOIN permissions P ON D.id = P.domain
        WHERE (P.userid=:user1 OR :user2) AND 
        (D.name LIKE :name1 OR :name2) AND
        (D.type=:type1 OR :type2)
    ";

    $stmt = $db->prepare($sql);

    if(isset($input->name)) {
        $name_filter = "%" . $input->name . "%";
        $name_filter_used = 0;
    } else {
        $name_filter = "";
        $name_filter_used = 1;
    }

    $id_filter = $_SESSION['id'];
    $id_filter_used = (int)($_SESSION['type'] == "admin" ? 1 : 0);

    if(isset($input->type)) {
        $type_filter = $input->type;
        $type_filter_used = 0;
    } else {
        $type_filter = "";
        $type_filter_used = 1;
    }

    $stmt->bindValue(':user1', $id_filter, PDO::PARAM_STR);
    $stmt->bindValue(':user2', $id_filter_used, PDO::PARAM_INT);
    $stmt->bindValue(':name1', $name_filter, PDO::PARAM_STR);
    $stmt->bindValue(':name2', $name_filter_used, PDO::PARAM_INT);
    $stmt->bindValue(':type1', $type_filter, PDO::PARAM_INT);
    $stmt->bindValue(':type2', $type_filter_used, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchColumn();
    
    if ($result == 0) {
        $result = 1;
    }
	
    // Initialize the return value
    $retval = Array();
    
    $retval['pages']['current'] = $input->page;
    $retval['pages']['total'] =  ceil($result / $config['domain_rows']);


    // Now the real search is done on the database
    $sql = "
        SELECT D.id,D.name,D.type,count(R.domain_id) AS records
        FROM domains D
        LEFT OUTER JOIN records R ON D.id = R.domain_id
        LEFT OUTER JOIN permissions P ON D.id = P.domain
        WHERE (P.userid=:user1 OR :user2)
        GROUP BY D.id, D.name, D.type
        HAVING
        (D.name LIKE :name1 OR :name2) AND
        (D.type=:type1 OR :type2)
    ";

    if(isset($input->sort->field) && $input->sort->field != "") {
        if($input->sort->field == "id") {
            $sql .= "ORDER BY id";
        } else if($input->sort->field == "name") {
            $sql .= "ORDER BY name";
        } else if($input->sort->field == "type") {
            $sql .= "ORDER BY type";
        } else if($input->sort->field == "records") {
            $sql .= "ORDER BY records";
        }

        if(isset($input->sort->order)) {
            if($input->sort->order == 0) {
                $sql .= " DESC";
            } else if($input->sort->order == 1) {
                $sql .= " ASC";
            }
        }
    }
    
    /*
     * Now the number of entries gets limited to the domainRows config value.
     * SQL LIMIT and OFFSET is used for that:
     * LIMIT upper OFFSET lower
     * Note that LIMIT 5 OFFSET 0 returns the first five rows!
     */
    $lower_limit = ($config['domain_rows'] * ($input->page - 1));
    
    $sql .= " LIMIT " . $config['domain_rows'] . " OFFSET " . $lower_limit;
    
    $stmt = $db->prepare($sql);

    if(isset($input->name)) {
        $name_filter = "%" . $input->name . "%";
        $name_filter_used = 0;
    } else {
        $name_filter = "";
        $name_filter_used = 1;
    }

    $id_filter = $_SESSION['id'];
    $id_filter_used = (int)($_SESSION['type'] == "admin" ? 1 : 0);

    if(isset($input->type)) {
        $type_filter = $input->type;
        $type_filter_used = 0;
    } else {
        $type_filter = "";
        $type_filter_used = 1;
    }

    $stmt->bindValue(':user1', $id_filter, PDO::PARAM_STR);
    $stmt->bindValue(':user2', $id_filter_used, PDO::PARAM_INT);
    $stmt->bindValue(':name1', $name_filter, PDO::PARAM_STR);
    $stmt->bindValue(':name2', $name_filter_used, PDO::PARAM_INT);
    $stmt->bindValue(':type1', $type_filter, PDO::PARAM_INT);
    $stmt->bindValue(':type2', $type_filter_used, PDO::PARAM_INT);
    $stmt->execute();

    while($obj = $stmt->fetchObject()) {
        $retval['data'][] = $obj;
    }
}

if(isset($input->action) && $input->action == "deleteDomain") {
    $domainId = $input->id;
    
    $db->beginTransaction();
    
    $stmt = $db->prepare("DELETE FROM permissions WHERE domain=:domain_id");
    $stmt->bindValue(':domain_id', $domainId, PDO::PARAM_INT);
    $stmt->execute();
    
    $stmt = $db->prepare("DELETE FROM remote WHERE record IN (SELECT id FROM records WHERE domain_id=:domain_id)");
    $stmt->bindValue(':domain_id', $domainId, PDO::PARAM_INT);
    $stmt->execute();
    
    $stmt = $db->prepare("DELETE FROM records WHERE domain_id=:domain_id");
    $stmt->bindValue(':domain_id', $domainId, PDO::PARAM_INT);
    $stmt->execute();
    
    $stmt = $db->prepare("DELETE FROM domains WHERE id=:domain_id");
    $stmt->bindValue(':domain_id', $domainId, PDO::PARAM_INT);
    $stmt->execute();
    
    $db->commit();
}

if(isset($retval)) {
    echo json_encode($retval);
} else {
    echo "{}";
}
