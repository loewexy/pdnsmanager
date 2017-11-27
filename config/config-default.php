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

//Database settings
$config['db_type'] = "mysql";
$config['db_host'] = "localhost";
$config['db_user'] = "root";
$config['db_password'] = "";
$config['db_port'] = 3306;
$config['db_name'] = "pdnsmanager";

// Authentication source
$config['auth_type'] = 'db';

// LDAP settings
$config['ldap_uri'] = 'ldapi:///';
$config['ldap_version'] = 3;
$config['ldap_starttls'] = false;
$config['ldap_bind_dn'] = '';
$config['ldap_bind_pw'] = '';
$config['ldap_base_dn'] = '';
$config['ldap_search'] = 'uid=%user%';

//Remote update
$config['nonce_lifetime'] = 15;

//Number of rows in domain overview
$config['domain_rows'] = 15;

// If config-user.php does not exist, redirect to the setup page
if(!(include 'config-user.php')) {
  Header("Location: install.php");
}
