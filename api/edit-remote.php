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
    $permquery = $db->prepare("SELECT COUNT(*) FROM records JOIN permissions ON records.domain_id=permissions.domain WHERE userid=:user AND records.id=:id");
	$permquery->bindValue(':user', $_SESSION['id'], PDO::PARAM_INT);
	$permquery->bindValue(':id', $input->record, PDO::PARAM_INT);
    $permquery->execute();
    if($permquery->fetchColumn() < 1 && $_SESSION['type'] != "admin") {
        echo "Permission denied!";
        exit();
    }
} else {
    echo "Permission denied!";
    exit();
}

//Action for getting permission
if(isset($input->action) && $input->action == "getPermissions") {

    $sql = "SELECT id, description, type FROM remote WHERE record=:record";
    $stmt = $db->prepare($sql);
    
	$stmt->bindValue(':record', $input->record, PDO::PARAM_INT);
    $stmt->execute();

    $retval = Array();

    while($obj = $stmt->fetchObject()) {
        $retval[] = $obj;
    }

}

//Action for adding password
if(isset($input->action) && $input->action == "addPassword") {
    $passwordHash = password_hash($input->password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO remote(record,description,type,security) VALUES (:record,:description,'password',:security)";
    $stmt = $db->prepare($sql);
    
	$stmt->bindValue(':record', $input->record, PDO::PARAM_INT);
	$stmt->bindValue(':description', $input->description, PDO::PARAM_STR);
	$stmt->bindValue(':security', $passwordHash, PDO::PARAM_STR);
    $stmt->execute();
}

//Action for adding key
if(isset($input->action) && $input->action == "addKey") { 
    $sql = "INSERT INTO remote(record,description,type,security) VALUES (:record,:description,'key',:security)";
    $stmt = $db->prepare($sql);
    
	$stmt->bindValue(':record', $input->record, PDO::PARAM_INT);
	$stmt->bindValue(':description', $input->description, PDO::PARAM_STR);
	$stmt->bindValue(':security', $input->key, PDO::PARAM_STR);
    $stmt->execute();
}

//Action for updating password
if(isset($input->action) && $input->action == "changePassword") {
    if(isset($input->password)) {
        $passwordHash = password_hash($input->password, PASSWORD_DEFAULT);
        $sql = "UPDATE remote SET description=:description,security=:security WHERE id=:id";
        $stmt = $db->prepare($sql);
		$stmt->bindValue(':description', $input->description, PDO::PARAM_STR);
		$stmt->bindValue(':security', $passwordHash, PDO::PARAM_STR);
		$stmt->bindValue(':id', $input->permission, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $sql = "UPDATE remote SET description=:description WHERE id=:id";
        $stmt = $db->prepare($sql);
		$stmt->bindValue(':description', $input->description, PDO::PARAM_STR);
        $stmt->bindValue(':id', $input->permission, PDO::PARAM_INT);
        $stmt->execute();
    }
}

//Action for updating key
if(isset($input->action) && $input->action == "changeKey") { 
    $sql = "UPDATE remote SET description=:description,security=:security WHERE id=:id";
    $stmt = $db->prepare($sql);
	
	$stmt->bindValue(':description', $input->description, PDO::PARAM_STR);
	$stmt->bindValue(':security', $input->key, PDO::PARAM_STR);
	$stmt->bindValue(':id', $input->permission, PDO::PARAM_INT);
    $stmt->execute();
}

//Action for getting key
if(isset($input->action) && $input->action == "getKey") { 
    $sql = "SELECT security FROM remote WHERE id=:id AND type='key' LIMIT 1";
    $stmt = $db->prepare($sql);
	$stmt->bindValue(':id', $input->permission, PDO::PARAM_INT);
    $stmt->execute();
    $key = $stmt->fetchColumn();
    
    $retval = Array();
    $retval['key'] = $key;
}

//Action for deleting permission
if(isset($input->action) && $input->action == "deletePermission") {
    $sql = "DELETE FROM remote WHERE id=:id";
    $stmt = $db->prepare($sql);
    
    $stmt->bindValue(':id', $input->permission, PDO::PARAM_INT);
    $stmt->execute();
}

if(isset($retval)) {
    echo json_encode($retval);
} else {
    echo "{}";
}
