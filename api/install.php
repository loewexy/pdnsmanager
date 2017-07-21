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

if(!(is_writable("../config"))) {
    $retval['status'] = "error";
    $retval['message'] = "Can't write to the config directory, please check the file system permissions";
    die(json_encode($retval));
}

//Get input
$input = json_decode(file_get_contents('php://input'));
//Database command
$sql["mysql"] = "
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
  KEY domain_id (domain_id),
  CONSTRAINT records_ibfk_1 FOREIGN KEY (domain_id) REFERENCES domains (id) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS users (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(50) NOT NULL,
  password varchar(200) NOT NULL,
  type varchar(20) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY user_name_index (name)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS permissions (
  userid int(11) NOT NULL,
  domain int(11) NOT NULL,
  PRIMARY KEY (userid,domain),
  KEY domain (domain),
  CONSTRAINT permissions_ibfk_1 FOREIGN KEY (domain) REFERENCES domains (id) ON DELETE CASCADE,
  CONSTRAINT permissions_ibfk_2 FOREIGN KEY (userid) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS remote (
    id int(11) NOT NULL AUTO_INCREMENT,
    record int(11) NOT NULL,
    description varchar(255) NOT NULL,
    type varchar(20) NOT NULL,
    security varchar(2000) NOT NULL,
    nonce varchar(255) DEFAULT NULL,
    PRIMARY KEY (id),
    KEY record (record),
    CONSTRAINT remote_ibfk_1 FOREIGN KEY (record) REFERENCES records (id) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS options (
    name varchar(255) NOT NULL,
    value varchar(2000) DEFAULT NULL,
    PRIMARY KEY (name)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
DELETE FROM options where name='schema_version';
INSERT INTO options(name,value) VALUES ('schema_version', 4);
";
$sql["pgsql"]="
CREATE TABLE IF NOT EXISTS domains (
  id                    SERIAL PRIMARY KEY,
  name                  VARCHAR(255) NOT NULL,
  master                VARCHAR(128) DEFAULT NULL,
  last_check            INT DEFAULT NULL,
  type                  VARCHAR(6) NOT NULL,
  notified_serial       INT DEFAULT NULL,
  account               VARCHAR(40) DEFAULT NULL,
  CONSTRAINT c_lowercase_name CHECK (((name)::TEXT = LOWER((name)::TEXT)))
);
CREATE UNIQUE INDEX IF NOT EXISTS name_index ON domains(name);
CREATE TABLE IF NOT EXISTS records (
  id                    SERIAL PRIMARY KEY,
  domain_id             INT DEFAULT NULL,
  name                  VARCHAR(255) DEFAULT NULL,
  type                  VARCHAR(10) DEFAULT NULL,
  content               VARCHAR(65535) DEFAULT NULL,
  ttl                   INT DEFAULT NULL,
  prio                  INT DEFAULT NULL,
  change_date           INT DEFAULT NULL,
  disabled              BOOL DEFAULT 'f',
  ordername             VARCHAR(255),
  auth                  BOOL DEFAULT 't',
  CONSTRAINT domain_exists
  FOREIGN KEY(domain_id) REFERENCES domains(id)
  ON DELETE CASCADE,
  CONSTRAINT c_lowercase_name CHECK (((name)::TEXT = LOWER((name)::TEXT)))
);
CREATE INDEX IF NOT EXISTS rec_name_index ON records(name);
CREATE INDEX IF NOT EXISTS nametype_index ON records(name,type);
CREATE INDEX IF NOT EXISTS domain_id ON records(domain_id);
CREATE INDEX IF NOT EXISTS recordorder ON records (domain_id, ordername text_pattern_ops);
CREATE TABLE IF NOT EXISTS users (
  id 				SERIAL PRIMARY KEY,
  name				varchar(50) NOT NULL,
  password			varchar(200) NOT NULL,
  type				varchar(20) NOT NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS user_name_index ON users(name);
CREATE TABLE IF NOT EXISTS permissions (
  userid				INT NOT NULL,
  domain			INT NOT NULL,
  PRIMARY KEY (userid,domain),
  CONSTRAINT domain_exists
  FOREIGN KEY(domain) REFERENCES domains(id)
  ON DELETE CASCADE,
  CONSTRAINT user_exists
  FOREIGN KEY(userid) REFERENCES users(id)
  ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS perm_domain_index ON permissions(domain);
CREATE INDEX IF NOT EXISTS perm_userid_index ON permissions(userid);
CREATE TABLE IF NOT EXISTS remote (
  id 				SERIAL PRIMARY KEY,
  record			INT NOT NULL,
  description		varchar(255) NOT NULL,
  type				varchar(20) NOT NULL,
  security			varchar(2000) NOT NULL,
  nonce				varchar(255) DEFAULT NULL,
  CONSTRAINT record_exists
  FOREIGN KEY(record) REFERENCES records(id)
  ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS rem_record_index ON remote(record);
CREATE TABLE IF NOT EXISTS options (
    name varchar(255) NOT NULL,
    value varchar(2000) DEFAULT NULL,
    PRIMARY KEY (name)
);
DELETE FROM options where name='schema_version';
INSERT INTO options(name,value) VALUES ('schema_version', 4);
";
try {
	$db = new PDO("$input->type:dbname=$input->database;host=$input->host;port=" . intval($input->port), $input->user, $input->password);
}
catch (PDOException $e) {
    $retval['status'] = "error";
    $retval['message'] = serialize($e);
}
if (!isset($retval)) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $passwordHash = password_hash($input->userPassword, PASSWORD_DEFAULT);
    $queries = explode(";", $sql[$input->type]);
    $db->beginTransaction();
    foreach ($queries as $query) {
        if (preg_replace('/\s+/', '', $query) != '') {
            $db->exec($query);
        }
    }
    $db->commit();
    $stmt = $db->prepare("INSERT INTO users(name,password,type) VALUES (:user,:hash,'admin')");
    $stmt->bindValue(':user', $input->userName, PDO::PARAM_STR);
    $stmt->bindValue(':hash', $passwordHash, PDO::PARAM_STR);
    $stmt->execute();
    $configFile = Array();
    $configFile[] = '<?php';
    $configFile[] = '$config[\'db_host\'] = \'' . addslashes($input->host) . "';";
    $configFile[] = '$config[\'db_user\'] = \'' . addslashes($input->user) . "';";
    $configFile[] = '$config[\'db_password\'] = \'' . addslashes($input->password) . "';";
    $configFile[] = '$config[\'db_name\'] = \'' . addslashes($input->database) . "';";
    $configFile[] = '$config[\'db_port\'] = ' . intval($input->port) . ";";
    $configFile[] = '$config[\'db_type\'] = \'' . addslashes($input->type) . "';";
    $retval['status'] = "success";
    try {
        file_put_contents("../config/config-user.php", implode("\n", $configFile));	
    }
    catch (Exception $e) {
        $retval['status'] = "error";
        $retval['message'] = serialize($e);
    }
}
if(isset($retval)) {
    echo json_encode($retval);
} else {
    echo "{}";
}
