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

if(isset($input->action) && $input->action == "addUser") {
    $passwordHash = password_hash($input->password, PASSWORD_DEFAULT);
    
    $db->autocommit(false);
    
    $stmt = $db->prepare("INSERT INTO user(name,password,type) VALUES (?,?,?)");
    $stmt->bind_param("sss", $input->name, $passwordHash, $input->type);
    $stmt->execute();
    $stmt->close();
    
    $stmt = $db->prepare("SELECT LAST_INSERT_ID()");
    $stmt->execute();
    $stmt->bind_result($newUserId);
    $stmt->fetch();
    $stmt->close();
    
    $db->commit();
    
    $retval = Array();
    $retval['newId'] = $newUserId;
}

if(isset($input->action) && $input->action == "getUserData") {
    $stmt = $db->prepare("SELECT name,type FROM user WHERE id=?");
    $stmt->bind_param("i", $input->id);
    $stmt->execute();
    $stmt->bind_result($userName, $userType);
    $stmt->fetch();
    $stmt->close();
    
    $retval = Array();
    $retval['name'] = $userName;
    $retval['type'] = $userType;
}

if(isset($input->action) && $input->action == "saveUserChanges") {
    if(isset($input->password)) {
        $passwordHash = password_hash($input->password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE user SET name=?,password=?,type=? WHERE id=?");
        $stmt->bind_param("sssi", $input->name, $passwordHash, $input->type, $input->id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $db->prepare("UPDATE user SET name=?,type=? WHERE id=?");
        $stmt->bind_param("ssi", $input->name, $input->type, $input->id);
        $stmt->execute();
        $stmt->close();
    }
}

if(isset($input->action) && $input->action == "getPermissions") {

    $stmt = $db->prepare("
        SELECT D.id,D.name 
        FROM permissions P
        JOIN domains D ON P.domain=D.id
        WHERE P.user=?
    ");
    
    $stmt->bind_param("i", $input->id);
    $stmt->execute();

    $result = $stmt->get_result();

    $retval = Array();

    while($obj = $result->fetch_object()) {
        $retval[] = $obj;
    }
}

if(isset($input->action) && $input->action == "removePermission") {

    $stmt = $db->prepare("DELETE FROM permissions WHERE user=? AND domain=?");
    
    $stmt->bind_param("ii", $input->userId, $input->domainId);
    $stmt->execute();
}

if(isset($input->action) && $input->action == "searchDomains" && isset($input->term)) {
    $stmt = $db->prepare("SELECT id,name AS text FROM domains WHERE name LIKE ?  AND id NOT IN(SELECT domain FROM permissions WHERE user=?)");

    $searchTerm = "%" . $input->term . "%";
    
    $stmt->bind_param("si", $searchTerm, $input->userId);
    $stmt->execute();    
    $result = $stmt->get_result();

    $retval = Array();

    while($obj = $result->fetch_object()) {
        $retval[] = $obj;
    }
}

if(isset($input->action) && $input->action == "addPermissions") {
    $stmt = $db->prepare("INSERT INTO permissions(user,domain) VALUES (?,?)");

    foreach($input->domains as $domain) {
        $stmt->bind_param("ii", $input->userId, $domain);
        $stmt->execute();
    }
}

if(isset($retval)) {
    echo json_encode($retval);
} else {
    echo "{}";
}
