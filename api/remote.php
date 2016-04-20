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
require_once '../lib/update-serial.php';

if(filter_input(INPUT_SERVER, "REQUEST_METHOD") == "GET") {
    if(filter_input(INPUT_GET, "action") == "updateRecord") {
        $input_domain = filter_input(INPUT_GET, "domain");
        $input_id = filter_input(INPUT_GET, "id");
        $input_password = filter_input(INPUT_GET, "password");
        $input_content = filter_input(INPUT_GET, "content");

        $stmt = $db->prepare("SELECT security,record FROM remote WHERE type='password' AND id=?");
        $stmt->bind_param("i", $input_id);
        $stmt->execute();
        $stmt->bind_result($passwordHash, $record);
        $stmt->fetch();
        $stmt->close();

        if(!password_verify($input_password, $passwordHash)) {
            $return['status'] = "error";
            $return['error'] = "Permission denied";
            echo json_encode($return);
            exit();
        }

        $stmt = $db->prepare("UPDATE records SET content=? WHERE name=? AND id=?");
        $stmt->bind_param("ssi", $input_content, $input_domain, $record);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare("SELECT domain_id FROM records WHERE id=?");
        $stmt->bind_param("i",$record);
        $stmt->execute();
        $stmt->bind_result($domain_id);
        $stmt->fetch();
        $stmt->close();

        update_serial($db, $domain_id);

        $return['status'] = "success";
        echo json_encode($return);
        exit();
    } else if(filter_input(INPUT_GET, "action") == "getIp") {
        // If we are behind a proxy, return the first IP the request was forwarded for.
        if(filter_input(INPUT_SERVER, "HTTP_X_FORWARDED_FOR") != null){
            $return['ip'] = explode(",", filter_input(INPUT_SERVER, "HTTP_X_FORWARDED_FOR"))[0];

        } else {
            $return['ip'] = filter_input(INPUT_SERVER, "REMOTE_ADDR");
        }

        echo json_encode($return);
        exit();
    }
} else if(filter_input(INPUT_SERVER, "REQUEST_METHOD") == "POST") {
    $input = json_decode(file_get_contents('php://input'));
    
    if(isset($input->domain) && isset($input->id) && isset($input->content)) {
        $stmt = $db->prepare("SELECT E.name,E.id FROM remote R JOIN records E ON R.record = E.id WHERE R.id=?");
        $stmt->bind_param("i", $input->id);
        $stmt->execute();
        $stmt->bind_result($domainName, $record);
        $stmt->fetch();
        $stmt->close();

        if($domainName != $input->domain) {
            $return['status'] = "error";
            $return['error'] = "Id and domain do not match!";
            echo json_encode($return);
            exit();
        }
        
        if(isset($_GET['getNonce'])) {
            $newNonce = base64_encode(openssl_random_pseudo_bytes(32));
            $dbNonce = $newNonce . ":" . time();
            
            $stmt = $db->prepare("UPDATE remote SET nonce=? WHERE id=?");
            $stmt->bind_param("si", $dbNonce, $input->id);
            $stmt->execute();
            $stmt->close();
            
            $return['nonce'] = $newNonce;
            echo json_encode($return);
            exit();
        } else if(isset($_GET['editRecord'])) {
            $stmt = $db->prepare("SELECT security,nonce FROM remote WHERE id=?");
            $stmt->bind_param("i", $input->id);
            $stmt->execute();
            $stmt->bind_result($pubkey, $dbNonce);
            $stmt->fetch();
            $stmt->close();
            
            $nonce = explode(":", $dbNonce);
            
            if($dbNonce == NULL || (time() - $nonce[1]) > $config['nonce_lifetime']) {
                $return['status'] = "error";
                $return['error'] = "No valid nonce available!";
                echo json_encode($return);
                exit();
            }
            
            $verifyString = $input->domain . $input->id . $input->content . $nonce[0];
            $signature = base64_decode($input->signature);
            
            if(openssl_verify($verifyString, $signature, $pubkey, OPENSSL_ALGO_SHA512) != 1) {
                $return['status'] = "error";
                $return['error'] = "Bad signature!";
                echo json_encode($return);
                exit();
            }
            
            $stmt = $db->prepare("UPDATE records SET content=? WHERE name=? AND id=?");
            $stmt->bind_param("ssi", $input->content, $input->domain, $record);
            $stmt->execute();
            $stmt->close();

            $stmt = $db->prepare("SELECT domain_id FROM records WHERE id=?");
            $stmt->bind_param("i",$record);
            $stmt->execute();
            $stmt->bind_result($domain_id);
            $stmt->fetch();
            $stmt->close();

            update_serial($db, $domain_id);

            $return['status'] = "success";
            echo json_encode($return);
            exit();
        } else {
            $return['status'] = "error";
            $return['error'] = "Wrong action";
            echo json_encode($return);
            exit();
        }
        
    } else {
        $return['status'] = "error";
        $return['error'] = "Missing data";
        echo json_encode($return);
        exit();
    }
}
