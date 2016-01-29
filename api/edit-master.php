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

//Action for getting SOA
if(isset($input->action) && $input->action == "getSoa") {
    $domainId = (int)$input->domain;
    
    $stmt = $db->prepare("SELECT content FROM records WHERE type='SOA' AND domain_id=?");
    $stmt->bind_param("i", $domainId);
    $stmt->execute();
    
    $stmt->bind_result($content);
    $stmt->fetch();
    
    $content = explode(" ", $content);
    
    $retval = Array();
    
    $retval['primary'] = preg_replace('/\\.$/', "", $content[0]);
    $retval['email'] = soa_to_mail($content[1]);
    $retval['serial'] = $content[2];
    $retval['refresh'] = $content[3];
    $retval['retry'] = $content[4];
    $retval['expire'] = $content[5];
    $retval['ttl'] = $content[6];
    
    
}

//Action for getting SOA
if(isset($input->action) && $input->action == "getSerial") {
    $domainId = (int)$input->domain;
    
    $stmt = $db->prepare("SELECT content FROM records WHERE type='SOA' AND domain_id=?");
    $stmt->bind_param("i", $domainId);
    $stmt->execute();
    
    $stmt->bind_result($content);
    $stmt->fetch();
    
    $content = explode(" ", $content);
    
    $retval = Array();
    
    $retval['serial'] = $content[2];
}

//Action for saving SOA
if(isset($input->action) && $input->action == "saveSoa") {
    $domainId = (int)$input->domain;
    
    $db->autocommit(false);
    $db->begin_transaction();
    
    $stmt = $db->prepare("SELECT content FROM records WHERE type='SOA' AND domain_id=?");
    $stmt->bind_param("i", $domainId);
    $stmt->execute();
    $stmt->bind_result($content);
    $stmt->fetch();
    $stmt->close();
    
    $content = explode(" ", $content);    
    $serial = $content[2];
        
    $newsoa = $input->primary . " ";
    $newsoa .= mail_to_soa($input->email) . " ";
    $newsoa .= $serial . " ";
    $newsoa .= $input->refresh . " ";
    $newsoa .= $input->retry . " ";
    $newsoa .= $input->expire . " ";
    $newsoa .= $input->ttl;
    
    $stmt = $db->prepare("UPDATE records SET content=?,ttl=? WHERE type='SOA' AND domain_id=?");
    $stmt->bind_param("sii", $newsoa, $input->ttl, $domainId);
    $stmt->execute();
    
    $db->commit();
    
    $retval = Array();
    
    update_serial($db, $domainId);
}

//Action for saving Record
if(isset($input->action) && $input->action == "saveRecord") {
    $domainId = $input->domain;
    
    $stmt = $db->prepare("UPDATE records SET name=?,type=?,content=?,ttl=?,prio=? WHERE id=? AND domain_id=?");
    $stmt->bind_param("sssiiii",
                $input->name, $input->type,
                $input->content, $input->ttl,
                $input->prio,
                $input->id, $domainId
            );
    $stmt->execute();
    update_serial($db, $domainId);
}

//Action for adding Record
if(isset($input->action) && $input->action == "addRecord") {
    $domainId = $input->domain;
    
    $stmt = $db->prepare("INSERT INTO records (domain_id, name, type, content, prio, ttl) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("isssii",
                $domainId, $input->name,
                $input->type, $input->content,
                $input->prio, $input->ttl
            );
    $stmt->execute();
    $stmt->close();
    
    $stmt = $db->prepare("SELECT LAST_INSERT_ID()");
    $stmt->execute();
    $stmt->bind_result($newId);
    $stmt->fetch();
    $stmt->close();
    
    $retval = Array();
    $retval['newId'] = $newId;
    
    update_serial($db, $domainId);
}

//Action for removing Record
if(isset($input->action) && $input->action == "removeRecord") {
    $domainId = $input->domain;
    $recordId = $input->id;
    
    $stmt = $db->prepare("DELETE FROM records WHERE id=? AND domain_id=?");
    $stmt->bind_param("ii", $recordId, $domainId);
    $stmt->execute();
    $stmt->close();
    
    update_serial($db, $domainId);
}

//Action for getting domain name
if(isset($input->action) && $input->action == "getDomainName") {
    $domainId = $input->domain;
    
    $stmt = $db->prepare("SELECT name FROM domains WHERE id=?");
    $stmt->bind_param("i", $domainId);
    $stmt->execute();
    $stmt->bind_result($domainName);
    $stmt->fetch();
    $stmt->close();
    
    $retval = Array();
    $retval['name'] = $domainName;
}

if (isset($retval)) {
    echo json_encode($retval);
} else {
    echo "{}";
}
