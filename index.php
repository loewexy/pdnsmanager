<?php
    require_once 'lib/headers.php';
    require_once 'config/config-default.php';
    require_once 'lib/database.php';
    require_once 'lib/checkversion.php';

    if(!checkVersion($db)) {
        Header("Location: upgrade.php");
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
        <title>PDNS Manager</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <link href="include/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="include/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
        <link href="include/custom.css" rel="stylesheet">
        
        <script src="include/jquery.js"></script>
        <script src="include/bootstrap/js/bootstrap.min.js"></script>
        
        <script src="js/index.js"></script>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-static-top">
            <div class="container">
                <div class="navbar-brand">
                    PDNS Manager
                </div>
                <ul class="nav navbar-nav">
                </ul>
            </div>
        </nav>
        
        <div class="container">
            <div class="row vspacer-60"></div>
            <div class="row">
                <div class="col-md-3 col-md-offset-6">
                    <div class="alert alert-danger defaulthidden" id="alertLoginFailed" role="alert">
                        Username and/or password wrong!
                    </div>
                    <form id="formLogin">
                        <div class="form-group">
                            <label class="control-label" for="inputUser">Username</label>
                            <input type="text" class="form-control" id="inputUser" placeholder="Username">
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="inputPassword">Password</label>
                            <input type="password" class="form-control" id="inputPassword" placeholder="Password">
                        </div>
                        <button id="buttonSubmit" type="submit" class="btn btn-primary">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
