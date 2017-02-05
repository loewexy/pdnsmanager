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
require_once '../lib/update-serial.php';

$input = json_decode(file_get_contents('php://input'));

if(!isset($input->csrfToken) || $input->csrfToken !== $_SESSION['csrfToken']) {
    echo "Permission denied!";
    exit();
}

//Permission check
if(isset($input->domain)) {
    $permquery = $db->prepare("SELECT COUNT(*) FROM permissions WHERE userid=:user AND domain=:domain");
    $permquery->bindValue(':user', $_SESSION['id'], PDO::PARAM_INT);
    $permquery->bindValue(':domain', $input->domain, PDO::PARAM_INT);
    $permquery->execute();
    if($permquery->fetchColumn() < 1 && $_SESSION['type'] != "admin") {
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
            (name LIKE :name1 OR :name2) AND
            (content LIKE :content1 OR :content2) AND
            (domain_id = :domain_id) AND
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
    
    $stmt->bindValue(':name1', $name_filter, PDO::PARAM_STR);
    $stmt->bindValue(':name2', $name_filter_used, PDO::PARAM_INT);
    $stmt->bindValue(':content1', $content_filter, PDO::PARAM_STR);
    $stmt->bindValue(':content2', $content_filter_used, PDO::PARAM_INT);
    $stmt->bindValue(':domain_id', $domainId, PDO::PARAM_INT);
    $stmt->execute();

    $retval = Array();

    while($obj = $stmt->fetchObject()) {
        $retval[] = $obj;
    }

}

//Action for getting SOA
if(isset($input->action) && $input->action == "getSoa") {
    $domainId = (int)$input->domain;
    
    $stmt = $db->prepare("SELECT content FROM records WHERE type='SOA' AND domain_id=:domain_id LIMIT 1");
    $stmt->bindValue(':domain_id', $domainId, PDO::PARAM_INT);
    $stmt->execute();
    
    $content = $stmt->fetchColumn();
    
    $content = explode(" ", $content);
    
    $retval = Array();
    
    $retval['primary'] = $content[0];
    $retval['email'] = soa_to_mail($content[1]);
    $retval['serial'] = $content[2];
    $retval['refresh'] = $content[3];
    $retval['retry'] = $content[4];
    $retval['expire'] = $content[5];
    $retval['ttl'] = $content[6];
}

//Action for getting SOA Serial
if(isset($input->action) && $input->action == "getSerial") {
    $domainId = (int)$input->domain;
    
    $stmt = $db->prepare("SELECT content FROM records WHERE type='SOA' AND domain_id=:domain_id LIMIT 1");
    $stmt->bindValue(':domain_id', $domainId, PDO::PARAM_INT);
    $stmt->execute();
    
    $content = $stmt->fetchColumn();
    
    $content = explode(" ", $content);
    
    $retval = Array();
    
    $retval['serial'] = $content[2];
}

//Action for saving SOA
if(isset($input->action) && $input->action == "saveSoa") {
    $domainId = (int)$input->domain;
    
    $db->beginTransaction();
    
    $stmt = $db->prepare("SELECT content FROM records WHERE type='SOA' AND domain_id=:domain_id LIMIT 1");
    $stmt->bindValue(':domain_id', $domainId, PDO::PARAM_INT);
    $stmt->execute();
    $content = $stmt->fetchColumn();;
    
    $content = explode(" ", $content);    
    $serial = $content[2];
        
    $newsoa = strtolower(preg_replace('/\s+/', '', $input->primary)) . " ";
    $newsoa .= strtolower(mail_to_soa(preg_replace('/\s+/', '', $input->email))) . " ";
    $newsoa .= $serial . " ";
    $newsoa .= $input->refresh . " ";
    $newsoa .= $input->retry . " ";
    $newsoa .= $input->expire . " ";
    $newsoa .= $input->ttl;
    
    $stmt = $db->prepare("UPDATE records SET content=:content,ttl=:ttl WHERE type='SOA' AND domain_id=:domain_id");
    $stmt->bindValue(':content', $newsoa, PDO::PARAM_STR);
    $stmt->bindValue(':ttl', $input->ttl, PDO::PARAM_INT);
    $stmt->bindValue(':domain_id', $domainId, PDO::PARAM_INT);
    $stmt->execute();
    
    $db->commit();
    
    $retval = Array();
    
    update_serial($db, $domainId);
}

//Action for saving Record
if(isset($input->action) && $input->action == "saveRecord") {
    $domainId = $input->domain;
    $recordName = strtolower(preg_replace('/\s+/', '', $input->name));
    $recordContent = trim($input->content);

    $stmt = $db->prepare("UPDATE records SET name=:name,type=:type,content=:content,ttl=:ttl,prio=:prio WHERE id=:id AND domain_id=:domain_id");
    $stmt->bindValue(':name', $recordName, PDO::PARAM_STR);
    $stmt->bindValue(':type', $input->type, PDO::PARAM_STR);
    $stmt->bindValue(':content', $recordContent, PDO::PARAM_STR);
    $stmt->bindValue(':ttl', $input->ttl, PDO::PARAM_INT);
    $stmt->bindValue(':prio', $input->prio, PDO::PARAM_INT);
    $stmt->bindValue(':id', $input->id, PDO::PARAM_INT);
    $stmt->bindValue(':domain_id', $domainId, PDO::PARAM_INT);
    $stmt->execute();
    update_serial($db, $domainId);
}

//Action for adding Record
if(isset($input->action) && $input->action == "addRecord") {
    $domainId = $input->domain;
    $recordName = strtolower(preg_replace('/\s+/', '', $input->name));
    $recordContent = trim($input->content);

    $db->beginTransaction();

    $stmt = $db->prepare("INSERT INTO records (domain_id, name, type, content, prio, ttl) VALUES (:domain_id,:name,:type,:content,:prio,:ttl)");
    $stmt->bindValue(':domain_id', $domainId, PDO::PARAM_INT);
    $stmt->bindValue(':name', $recordName, PDO::PARAM_STR);
    $stmt->bindValue(':type', $input->type, PDO::PARAM_STR);
    $stmt->bindValue(':content', $recordContent, PDO::PARAM_STR);
    $stmt->bindValue(':ttl', $input->ttl, PDO::PARAM_INT);
    $stmt->bindValue(':prio', $input->prio, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = $db->prepare("SELECT MAX(id) FROM records WHERE domain_id=:domain_id AND name=:name AND type=:type AND content=:content AND prio=:prio AND ttl=:ttl");
    $stmt->bindValue(':domain_id', $domainId, PDO::PARAM_INT);
    $stmt->bindValue(':name', $recordName, PDO::PARAM_STR);
    $stmt->bindValue(':type', $input->type, PDO::PARAM_STR);
    $stmt->bindValue(':content', $recordContent, PDO::PARAM_STR);
    $stmt->bindValue(':ttl', $input->ttl, PDO::PARAM_INT);
    $stmt->bindValue(':prio', $input->prio, PDO::PARAM_INT);
    $stmt->execute();
    $newId = $stmt->fetchColumn();

    $db->commit();

    $retval = Array();
    $retval['newId'] = $newId;
    
    update_serial($db, $domainId);
}

//Action for removing Record
if(isset($input->action) && $input->action == "removeRecord") {
    $domainId = $input->domain;
    $recordId = $input->id;
    
    $stmt = $db->prepare("DELETE FROM records WHERE id=:id AND domain_id=:domain_id");
    $stmt->bindValue(':id', $recordId, PDO::PARAM_INT);
    $stmt->bindValue(':domain_id', $domainId, PDO::PARAM_INT);
    $stmt->execute();
    
    update_serial($db, $domainId);
}

//Action for getting domain name
if(isset($input->action) && $input->action == "getDomainName") {
    $domainId = $input->domain;
    
    $stmt = $db->prepare("SELECT name FROM domains WHERE id=:id LIMIT 1");
    $stmt->bindValue(':id', $domainId, PDO::PARAM_INT);
    $stmt->execute();
    $domainName = $stmt->fetchColumn();
    
    $retval = Array();
    $retval['name'] = $domainName;
}

if (isset($retval)) {
    echo json_encode($retval);
} else {
    echo "{}";
}
