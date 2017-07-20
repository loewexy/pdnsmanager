<?php
    require_once 'lib/headers.php';
    require_once 'config/config-default.php';
    require_once 'lib/database.php';
    require_once 'lib/checkversion.php';
    if(checkVersion($db)) {
        Header("Location: index.php");
    }
?>
<!DOCTYPE html>
<!--
Copyright 2016 Lukas Metzger <developer@lukas-metzger.com>.
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at
     http://www.apache.org/licenses/LICENSE-2.0
Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
-->
<html>
    <head>
        <title>PDNS Manager - Upgrade</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="include/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="include/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
        <link href="include/custom.css" rel="stylesheet">
        <script src="include/jquery.js"></script>
        <script src="include/bootstrap/js/bootstrap.min.js"></script>
        <script src="js/upgrade.js"></script>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-static-top">
            <div class="container">
                <div class="navbar-brand">
                    PDNS Manager
                </div>
                <ul class="nav navbar-nav">
                    <li><a href="#">Upgrade</a></li>
                </ul>
            </div>
        </nav>
        <div class="container">
            <row>
                <h2>Upgrade PDNS Manager</h2>
            </row>
            <row>
                An upgrade for your PDNS Manager database is available and must be installed!                
            </row>
            <div class="row vspacer-20"></div>
            <div class="col-md-6">
                <row class="row" id="row-button-start">
                    <button id="button-start" class="btn btn-primary">Start</button>
                </row>
                <row class="row" id="status">
                </row>
                <row class="row defaulthidden" id="row-button-home">
                    <a href="index.php" class="btn btn-primary">Login</a>
                </row>
            </div>
        </div>
    </body>
</html>
