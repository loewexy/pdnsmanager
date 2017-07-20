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
<?php
    require_once 'lib/headers.php';
    require_once 'lib/session.php';
    session_destroy();
    setcookie("authSecret", "", 1, "/", "", false, true);
?>
<html>
    <head>
        <title>PDNS Manager</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="include/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="include/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
        <link href="include/custom.css" rel="stylesheet">
        <script src="include/jquery.js"></script>
        <script src="include/bootstrap/js/bootstrap.min.js"></script>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-static-top">
            <div class="container">
                <a class="navbar-brand" href="index.php">PDNS Manager</a>
                <ul class="nav navbar-nav">
                </ul>
            </div>
        </nav>
        <div class="container">
            <div class="row vspacer-60"></div>
            <div class="row">
                <div class="col-md-6 col-md-offset-6">
                    <h4>You have been logged out successfully!</h4>
                    <a class="btn btn-primary" href="index.php">Login again</a>
                </div>
            </div>
        </div>
    </body>
</html>
