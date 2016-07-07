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

if(file_exists("../config/config-user.php")) {
    echo "Permission denied!";
    exit();
}
require_once("../config/config-default.php");

//Get input
$input = json_decode(file_get_contents('php://input'));

//Database command
$sql = "
CREATE TABLE IF NOT EXISTS domains (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  master varchar(128) DEFAULT NULL,
  last_check int(11) DEFAULT NULL,
  type varchar(6) NOT NULL,
  notified_serial int(11) DEFAULT NULL,
  account varchar(40) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY name_index (name)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS permissions (
  user int(11) NOT NULL,
  domain int(11) NOT NULL,
  PRIMARY KEY (user,domain),
  KEY domain (domain)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS records (
  id int(11) NOT NULL AUTO_INCREMENT,
  domain_id int(11) DEFAULT NULL,
  name varchar(255) DEFAULT NULL,
  type varchar(6) DEFAULT NULL,
  content varchar(255) DEFAULT NULL,
  ttl int(11) DEFAULT NULL,
  prio int(11) NOT NULL DEFAULT '0',
  change_date int(11) DEFAULT NULL,
  disabled TINYINT(1) DEFAULT 0,
  auth TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id),
  KEY rec_name_index (name),
  KEY nametype_index (name,type),
  KEY domain_id (domain_id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

ALTER TABLE records
  ADD CONSTRAINT records_ibfk_1 FOREIGN KEY (domain_id) REFERENCES domains (id) ON DELETE CASCADE;

CREATE TABLE IF NOT EXISTS user (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(50) NOT NULL,
  password varchar(200) NOT NULL,
  type varchar(20) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

ALTER TABLE permissions
  ADD CONSTRAINT permissions_ibfk_1 FOREIGN KEY (domain) REFERENCES domains (id) ON DELETE CASCADE;
ALTER TABLE permissions
  ADD CONSTRAINT permissions_ibfk_2 FOREIGN KEY (user) REFERENCES user (id) ON DELETE CASCADE;

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

ALTER TABLE remote
    ADD CONSTRAINT remote_ibfk_1 FOREIGN KEY (record) REFERENCES records (id) ON DELETE CASCADE;

CREATE TABLE IF NOT EXISTS options (
    name varchar(255) NOT NULL,
    value varchar(2000) DEFAULT NULL,
    PRIMARY KEY (name)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

INSERT INTO options(name,value) VALUES ('schema_version', 3);

CREATE TABLE domainmetadata (
    id INT AUTO_INCREMENT,
    domain_id INT NOT NULL,
    kind VARCHAR(32),
    content TEXT,
    PRIMARY KEY (id)
) Engine=InnoDB;
";


$db = @new mysqli($input->host, $input->user, $input->password, $input->database, $input->port);

    
if($db->connect_error) {
    $retval['status'] = "error";
    $retval['message'] = $db->connect_error;
} else {
    $passwordHash = password_hash($input->userPassword, PASSWORD_DEFAULT);
    
    $db->multi_query($sql);
    while ($db->next_result()) {;}
    
    $stmt = $db->prepare("INSERT INTO user(name,password,type) VALUES (?,?,'admin')");
    $stmt->bind_param("ss", $input->userName, $passwordHash);
    $stmt->execute();
    $stmt->close();
    
    $configFile = Array();
    
    $configFile[] = '<?php';
    $configFile[] = '$config[\'db_host\'] = \'' . addslashes($input->host) . "';";
    $configFile[] = '$config[\'db_user\'] = \'' . addslashes($input->user) . "';";
    $configFile[] = '$config[\'db_password\'] = \'' . addslashes($input->password) . "';";
    $configFile[] = '$config[\'db_name\'] = \'' . addslashes($input->database) . "';";
    $configFile[] = '$config[\'db_port\'] = ' . addslashes($input->port) . ";";
    
    file_put_contents("../config/config-user.php", implode("\n", $configFile));
    
    $retval['status'] = "success";
}


if(isset($retval)) {
    echo json_encode($retval);
} else {
    echo "{}";
}
