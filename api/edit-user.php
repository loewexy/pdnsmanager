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
    
    $db->beginTransaction();
    
    $stmt = $db->prepare("INSERT INTO users(name,password,type) VALUES (:name,:password,:type)");
    $stmt->bindValue(':name', $input->name, PDO::PARAM_STR);
    $stmt->bindValue(':password', $passwordHash, PDO::PARAM_STR);
    $stmt->bindValue(':type', $input->type, PDO::PARAM_STR);
    $stmt->execute();
    
    $stmt = $db->prepare("SELECT MAX(id) FROM users WHERE name=:name AND password=:password AND type=:type");
    $stmt->bindValue(':name', $input->name, PDO::PARAM_STR);
    $stmt->bindValue(':password', $passwordHash, PDO::PARAM_STR);
    $stmt->bindValue(':type', $input->type, PDO::PARAM_STR);
    $stmt->execute();
    $newUserId = $stmt->fetchColumn();
    
    $db->commit();
    
    $retval = Array();
    $retval['newId'] = $newUserId;
}

if(isset($input->action) && $input->action == "getUserData") {
    $stmt = $db->prepare("SELECT name,type FROM users WHERE id=:id LIMIT 1");
    $stmt->bindValue(':id', $input->id, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->bindColumn('name', $userName);
    $stmt->bindColumn('type', $userType);
    $stmt->fetch(PDO::FETCH_BOUND);
    
    $retval = Array();
    $retval['name'] = $userName;
    $retval['type'] = $userType;
}

if(isset($input->action) && $input->action == "saveUserChanges") {
    if(isset($input->password)) {
        $passwordHash = password_hash($input->password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET name=:name,password=:password,type=:type WHERE id=:id");
	$stmt->bindValue(':name', $input->name, PDO::PARAM_STR);
	$stmt->bindValue(':password', $passwordHash, PDO::PARAM_STR);
	$stmt->bindValue(':type', $input->type, PDO::PARAM_STR);
	$stmt->bindValue(':id', $input->id, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $stmt = $db->prepare("UPDATE users SET name=:name,type=:type WHERE id=:id");
	$stmt->bindValue(':name', $input->name, PDO::PARAM_STR);
	$stmt->bindValue(':type', $input->type, PDO::PARAM_STR);
	$stmt->bindValue(':id', $input->id, PDO::PARAM_INT);
        $stmt->execute();
    }
}

if(isset($input->action) && $input->action == "getPermissions") {

    $stmt = $db->prepare("
        SELECT D.id,D.name 
        FROM permissions P
        JOIN domains D ON P.domain=D.id
        WHERE P.userid=:user
    ");
    
    $stmt->bindValue(':user', $input->id, PDO::PARAM_INT);
    $stmt->execute();

    $retval = Array();

    while($obj = $stmt->fetchObject()) {
        $retval[] = $obj;
    }
}

if(isset($input->action) && $input->action == "removePermission") {

    $stmt = $db->prepare("DELETE FROM permissions WHERE userid=:user AND domain=:domain");
    
    $stmt->bindValue(':user', $input->userId, PDO::PARAM_INT);
    $stmt->bindValue(':domain', $input->domainId, PDO::PARAM_INT);
    $stmt->execute();
}

if(isset($input->action) && $input->action == "searchDomains" && isset($input->term)) {
    $stmt = $db->prepare("SELECT id,name AS text FROM domains WHERE name LIKE :name  AND id NOT IN(SELECT domain FROM permissions WHERE userid=:user)");

    $searchTerm = "%" . $input->term . "%";
    
    $stmt->bindValue(':name', $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(':user', $input->userId, PDO::PARAM_INT);
    $stmt->execute();

    $retval = Array();

    while($obj = $stmt->fetchObject()) {
        $retval[] = $obj;
    }
}

if(isset($input->action) && $input->action == "addPermissions") {
    $stmt = $db->prepare("INSERT INTO permissions(userid,domain) VALUES (:user,:domain)");

    foreach($input->domains as $domain) {
        $stmt->bindValue(':user', $input->userId, PDO::PARAM_INT);
        $stmt->bindValue(':domain', $domain, PDO::PARAM_INT);
        $stmt->execute();
    }
}

if(isset($retval)) {
    echo json_encode($retval);
} else {
    echo "{}";
}
