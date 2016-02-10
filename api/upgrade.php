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
require_once '../lib/checkversion.php';

$input = json_decode(file_get_contents('php://input'));

if(isset($input->action) && $input->action == "getVersions") {
    $retval['from'] = getVersion($db);
    $retval['to'] = getExpectedVersion();
}

if(isset($input->action) && $input->action == "requestUpgrade") {
    $currentVersion = getVersion($db);
    
    if($currentVersion < 1) {
        $sql = "
            CREATE TABLE IF NOT EXISTS remote (
                id int(11) NOT NULL AUTO_INCREMENT,
                record int(11) NOT NULL,
                description varchar(255) NOT NULL,
                type varchar(20) NOT NULL,
                security varchar(2000) NOT NULL,
                nonce varchar(255) DEFAULT NULL,
                PRIMARY KEY (id),
                KEY record (record)
            ) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
            
            ALTER TABLE `remote`
                ADD CONSTRAINT `remote_ibfk_1` FOREIGN KEY (`record`) REFERENCES `records` (`id`);
                
            CREATE TABLE IF NOT EXISTS options (
                name varchar(255) NOT NULL,
                value varchar(2000) DEFAULT NULL,
                PRIMARY KEY (name)
            ) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
            
            INSERT INTO options(name,value) VALUES ('schema_version', 1);
        ";
        
        $db->multi_query($sql);
        while ($db->next_result()) {;}
    }
    if($currentVersion < 2) {
        $sql = "
            ALTER TABLE permissions
              DROP FOREIGN KEY permissions_ibfk_1;
            ALTER TABLE permissions
              DROP FOREIGN KEY permissions_ibfk_2;
            ALTER TABLE permissions
              ADD CONSTRAINT permissions_ibfk_1 FOREIGN KEY (domain) REFERENCES domains (id) ON DELETE CASCADE;
            ALTER TABLE permissions
              ADD CONSTRAINT permissions_ibfk_2 FOREIGN KEY (user) REFERENCES user (id) ON DELETE CASCADE;
              
            ALTER TABLE remote
              DROP FOREIGN KEY remote_ibfk_1;
            ALTER TABLE remote
              ADD CONSTRAINT remote_ibfk_1 FOREIGN KEY (record) REFERENCES records (id) ON DELETE CASCADE;
              
            ALTER TABLE records
              ADD CONSTRAINT records_ibfk_1 FOREIGN KEY (domain_id) REFERENCES domains (id) ON DELETE CASCADE;
              
            UPDATE options SET value=2 WHERE name='schema_version';
        ";
        
        $db->multi_query($sql);
        while ($db->next_result()) {;}
    }
    
    $retval['status'] = "success";
}

if(isset($retval)) {
    echo json_encode($retval);
} else {
    echo "{}";
}
