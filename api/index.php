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

$sql = $db->prepare("SELECT id,password,type FROM user WHERE name=?");
$sql->bind_param("s", $input->user);
$sql->execute();

$sql->bind_result($id, $password, $type);
$sql->fetch();

if (password_verify($input->password, $password)) {
    $retval['status'] = "success";
    
    session_start();
    
    $_SESSION['id'] = $id;
    $_SESSION['type'] = $type;
} else {
    $retval['status'] = "fail";
}

echo json_encode($retval);