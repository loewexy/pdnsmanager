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
    $dbType = $config['db_type'];
    if($currentVersion < 1) {
        $sql["mysql"] = "
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
        $sql["pgsql"] = "INSERT INTO options(name,value) VALUES ('schema_version', 1);";
        $queries = explode(";", $sql[$dbType]);
        $db->beginTransaction();

        foreach ($queries as $query) {
            if (preg_replace('/\s+/', '', $query) != '') {
                $db->exec($query);
            }
        }

        $db->commit();
    }
    if($currentVersion < 2) {
        $sql["mysql"] = "
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
        $sql["pgsql"] = "UPDATE options SET value=2 WHERE name='schema_version';";
        $queries = explode(";", $sql[$dbType]);

        $db->beginTransaction();

        foreach ($queries as $query) {
            if (preg_replace('/\s+/', '', $query) != '') {
                $db->exec($query);
            }
        }

        $db->commit();
    }
    if($currentVersion < 3) {
        $sql["mysql"] = "
            CREATE TABLE IF NOT EXISTS domainmetadata (
                id INT AUTO_INCREMENT,
                domain_id INT NOT NULL,
                kind VARCHAR(32),
                content TEXT,
                PRIMARY KEY (id)
            ) Engine=InnoDB;
            
            ALTER TABLE records ADD disabled TINYINT(1) DEFAULT 0;
            ALTER TABLE records ADD auth TINYINT(1) DEFAULT 1;
            
            UPDATE options SET value=3 WHERE name='schema_version';
        ";
        $sql["pgsql"] = "UPDATE options SET value=3 WHERE name='schema_version';";

        $queries = explode(";", $sql[$dbType]);

        $db->beginTransaction();

        foreach ($queries as $query) {
            if (preg_replace('/\s+/', '', $query) != '') {
                $db->exec($query);
            }
        }

        $db->commit();
    }
    if($currentVersion < 4) {
        $sql["mysql"] = "
            RENAME TABLE user TO users;
            ALTER TABLE permissions CHANGE user userid INT(11);
            
            UPDATE options SET value=4 WHERE name='schema_version';
        ";
        $sql["pgsql"] = "UPDATE options SET value=4 WHERE name='schema_version';";

        $queries = explode(";", $sql[$dbType]);

        $db->beginTransaction();

        foreach ($queries as $query) {
            if (preg_replace('/\s+/', '', $query) != '') {
                $db->exec($query);
            }
        }

        $db->commit();
    }

    $retval['status'] = "success";
}

if(isset($retval)) {
    echo json_encode($retval);
} else {
    echo "{}";
}
