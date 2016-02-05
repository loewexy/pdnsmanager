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

//Permission check
if(isset($input->record)) {
    $permquery = $db->prepare("SELECT * FROM records JOIN permissions ON records.domain_id=permissions.domain WHERE user=? AND records.id=?");
    
    $permquery->bind_param("ii", $_SESSION['id'], $input->record);
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

//Action for getting permission
if(isset($input->action) && $input->action == "getPermissions") {

    $sql = "SELECT id, description, type FROM remote WHERE record=?";
    $stmt = $db->prepare($sql);
    
    $stmt->bind_param("i",$input->record);
    $stmt->execute();

    $result = $stmt->get_result();

    $retval = Array();

    while($obj = $result->fetch_object()) {
        $retval[] = $obj;
    }

}

//Action for adding password
if(isset($input->action) && $input->action == "addPassword") {
    $passwordHash = password_hash($input->password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO remote(record,description,type,security) VALUES (?,?,'password',?)";
    $stmt = $db->prepare($sql);
    
    $stmt->bind_param("iss",$input->record, $input->description, $passwordHash);
    $stmt->execute();
}

//Action for adding key
if(isset($input->action) && $input->action == "addKey") { 
    $sql = "INSERT INTO remote(record,description,type,security) VALUES (?,?,'key',?)";
    $stmt = $db->prepare($sql);
    
    $stmt->bind_param("iss",$input->record, $input->description, $input->key);
    $stmt->execute();
}

//Action for updating password
if(isset($input->action) && $input->action == "changePassword") {
    if(isset($input->password)) {
        $passwordHash = password_hash($input->password, PASSWORD_DEFAULT);
        $sql = "UPDATE remote SET description=?,security=? WHERE id=?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ssi",$input->description, $passwordHash, $input->permission);
        $stmt->execute();
    } else {
        $sql = "UPDATE remote SET description=? WHERE id=?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ssi",$input->description, $input->permission);
        $stmt->execute();
    }
}

//Action for updating key
if(isset($input->action) && $input->action == "changeKey") { 
    $sql = "UPDATE remote SET description=?,security=? WHERE id=?";
    $stmt = $db->prepare($sql);

    $stmt->bind_param("ssi",$input->description, $input->key, $input->permission);
    $stmt->execute();
}

//Action for getting key
if(isset($input->action) && $input->action == "getKey") { 
    $sql = "SELECT security FROM remote WHERE id=? AND type='key'";
    $stmt = $db->prepare($sql);
    
    $stmt->bind_param("i",$input->permission);
    $stmt->execute();
    $stmt->bind_result($key);
    $stmt->fetch();
    
    $retval = Array();
    $retval['key'] = $key;
}

//Action for deleting permission
if(isset($input->action) && $input->action == "deletePermission") {
    $sql = "DELETE FROM remote WHERE id=?";
    $stmt = $db->prepare($sql);
    
    $stmt->bind_param("i",$input->permission);
    $stmt->execute();
}

if(isset($retval)) {
    echo json_encode($retval);
} else {
    echo "{}";
}
