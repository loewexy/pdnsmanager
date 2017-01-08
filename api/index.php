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

$input = json_decode(file_get_contents('php://input'));

$stmt = $db->prepare("SELECT id,password,type FROM users WHERE name=:name LIMIT 1");
$stmt->bindValue(':name', $input->user, PDO::PARAM_STR);
$stmt->execute();
$stmt->bindColumn('id', $id);
$stmt->bindColumn('password', $password);
$stmt->bindColumn('type', $type);
$stmt->fetch(PDO::FETCH_BOUND);

if (password_verify($input->password, $password)) {
    $retval['status'] = "success";
    
    session_start();
    
    $_SESSION['id'] = $id;
    $_SESSION['type'] = $type;
    
    $randomSecret = base64_encode(openssl_random_pseudo_bytes(32));
    $_SESSION['secret'] = $randomSecret;
    setcookie("authSecret", $randomSecret, 0, "/", "", false, true);
    
    $csrfToken = base64_encode(openssl_random_pseudo_bytes(32));
    $_SESSION['csrfToken'] = $csrfToken;
} else {
    $retval['status'] = "fail";
}

echo json_encode($retval);
