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

//Permission check
if(isset($input->domain)) {
    $permquery = $db->prepare("SELECT * FROM permissions WHERE user=? AND domain=?");
    
    $permquery->bind_param("ii", $_SESSION['id'], $input->domain);
    $permquery->execute();
    $permquery->store_result();
    if($permquery->num_rows() < 1 && $_SESSION['type'] != "admin") {
        echo "Permission denied!";
        exit();
    }
} else {
    echo "Permission denied!";
    exit();
}


//Action for getting Records
if(isset($input->action) && $input->action == "getRecords") {

    $sql = "
        SELECT id,name,type,content,ttl,prio AS priority
        FROM records
        WHERE
            (name LIKE ? OR ?) AND
            (content LIKE ? OR ?) AND
            (domain_id = ?) AND
            (type != 'SOA')
    ";
    
    if(isset($input->type)) {
        $sql .= " AND type IN(";
        
        foreach($input->type as $filtertype) {
            $filtertype = $db->escape_string($filtertype);
            $sql .= "'" . $filtertype . "'" . ",";
        }
        $sql = rtrim($sql, ",");
        $sql .= ")";
    }

    if(isset($input->sort->field) && $input->sort->field != "") {
        if($input->sort->field == "id") {
            $sql .= " ORDER BY id";
        } else if($input->sort->field == "name") {
            $sql .= " ORDER BY name";
        } else if($input->sort->field == "type") {
            $sql .= " ORDER BY type";
        } else if($input->sort->field == "content") {
            $sql .= " ORDER BY content";
        } else if($input->sort->field == "ttl") {
            $sql .= " ORDER BY ttl";
        } else if($input->sort->field == "priority") {
            $sql .= " ORDER BY prio";
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

    if(isset($input->content)) {
        $content_filter = "%" . $input->content . "%";
        $content_filter_used = 0;
    } else {
        $content_filter = "";
        $content_filter_used = 1;
    }

    $domainId = (int)$input->domain;
    
    $stmt->bind_param("sisii",
            $name_filter, $name_filter_used,
            $content_filter, $content_filter_used,
            $domainId
    );
    $stmt->execute();

    $result = $stmt->get_result();

    $retval = Array();

    while($obj = $result->fetch_object()) {
        $retval[] = $obj;
    }

}

echo json_encode($retval);
