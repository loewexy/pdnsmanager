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
require_once '../lib/soa-mail.php';

$input = json_decode(file_get_contents('php://input'));

if(!isset($input->csrfToken) || $input->csrfToken !== $_SESSION['csrfToken']) {
    echo "Permission denied!";
    exit();
}

if(!isset($_SESSION['type']) || $_SESSION['type'] != "admin") {
    echo "Permission denied!";
    exit();
}

if(isset($input->action) && $input->action == "addDomain") {
    $soaData = Array();
    $soaData[] = $input->primary;
    $soaData[] = mail_to_soa($input->mail);
    $soaData[] = date("Ymd") . "00";
    $soaData[] = $input->refresh;
    $soaData[] = $input->retry;
    $soaData[] = $input->expire;
    $soaData[] = $input->ttl;
    
    $soaContent = implode(" ", $soaData);
    
    $db->autocommit(false);
    
    $stmt = $db->prepare("INSERT INTO domains(name,type) VALUES (?,?)");
    $stmt->bind_param("ss", $input->name, $input->type);
    $stmt->execute();
    $stmt->close();
    
    $stmt = $db->prepare("SELECT LAST_INSERT_ID()");
    $stmt->execute();
    $stmt->bind_result($newDomainId);
    $stmt->fetch();
    $stmt->close();
    
    $stmt = $db->prepare("INSERT INTO records(domain_id,name,type,content,ttl) VALUES (?,?,'SOA',?,?)");
    $stmt->bind_param("issi", $newDomainId, $input->name, $soaContent, $input->ttl);
    $stmt->execute();
    $stmt->close();
    
    $db->commit();
    
    $retval = Array();
    $retval['newId'] = $newDomainId;
}

if(isset($retval)) {
    echo json_encode($retval);
} else {
    echo "{}";
}
