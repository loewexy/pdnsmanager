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

function soa_to_mail($soa) {
    $tmp = preg_replace('/([^\\\\])\\./', '\\1@', $soa, 1);
    $tmp = preg_replace('/\\\\\\./', ".", $tmp);
    $tmp = preg_replace('/\\.$/', "", $tmp);
    
    return $tmp;
}

function mail_to_soa($mail) {
    $parts = explode("@", $mail);
    
    $parts[0] = str_replace(".", "\.", $parts[0]);
    
    $parts[] = "";
    
    return implode(".", $parts);
}