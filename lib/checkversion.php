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

function getExpectedVersion() {
    return 2;
}

function checkVersion($db) {
    if(getVersion($db) == getExpectedVersion()) {
        return true;
    } else {
        return false;
    }
}

function getVersion($db) {
    $stmt = $db->prepare("SHOW TABLES LIKE 'options'");
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows() < 1) {
        return 0;
    }
    $stmt->close();
    
    $stmt = $db->prepare("SELECT value FROM options WHERE name='schema_version'");
    $stmt->execute();
    $stmt->bind_result($version);
    $stmt->fetch();
    $stmt->close();
    
    return $version;    
}