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

if(isset($input->action) && $input->action == "changePassword") {
    $passwordHash = password_hash($input->password, PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("UPDATE \"user\" SET password=:password WHERE id=:id");
	$stmt->bindValue(':password', $passwordHash, PDO::PARAM_STR);
	$stmt->bindValue(':id', $_SESSION['id'], PDO::PARAM_INT);
    $stmt->execute();
}

if(isset($retval)) {
    echo json_encode($retval);
} else {
    echo "{}";
}
