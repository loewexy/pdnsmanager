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
    if(file_exists("config/config-user.php")) {
        Header("Location: index.php");
    }
?>
<html>
    <head>
        <title>PDNS Manager - Domains</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <link href="include/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="include/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
        <link href="include/custom.css" rel="stylesheet">
        
        <script src="include/jquery.js"></script>
        <script src="include/bootstrap/js/bootstrap.min.js"></script>
        
        <script src="js/install.js"></script>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-static-top">
            <div class="container">
                <div class="navbar-brand">
                    PDNS Manager
                </div>
                <ul class="nav navbar-nav">
                    <li><a href="#">Install</a></li>
                </ul>
            </div>
        </nav>
        
        <div class="container">
            
            <row>
                <h2 id="domain-name">Install PDNS Manager</h2>
            </row>
            
            <row>
                <div class="alert alert-danger defaulthidden" id="alertFailed" role="alert">
                    Error
                </div>
            </row>

            <row>
                
                <form>
                    <div class="container col-md-3">
                        <h3>Database</h3>
                        <div class="form-group">
                            <label for="dbType" class="control-label">Type</label>
							<select class="form-control" id="dbType">
								<option value="mysql" selected>MySQL</option>
								<option value="pgsql">PgSQL</option>
							</select>
                        </div>
                        <div class="form-group">
                            <label for="dbHost" class="control-label">Host</label>
                            <input type="text" class="form-control" id="dbHost" placeholder="Host" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="dbUser" class="control-label">User</label>
                            <input type="text" class="form-control" id="dbUser" placeholder="User" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="dbPassword" class="control-label">Password</label>
                            <input type="password" class="form-control" id="dbPassword" placeholder="Password" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="dbDatabase" class="control-label">Database</label>
                            <input type="text" class="form-control" id="dbDatabase" placeholder="Database" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="dbPort" class="control-label">Port</label>
                            <input type="text" class="form-control" id="dbPort" value="3306" autocomplete="off">
                        </div>
                        <button id="buttonInstall" class="btn btn-primary">Install</button>
                    </div>
                    
                    <div class="container col-md-3">
                        <h3>Admin</h3>
                        
                        <div class="form-group">
                            <label for="adminName" class="control-label">Name</label>
                            <input type="text" class="form-control" id="adminName" placeholder="Name" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="adminPassword" class="control-label">Password</label>
                            <input type="password" class="form-control" id="adminPassword" placeholder="Password" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="adminPassword2" class="control-label">Password repeated</label>
                            <input type="password" class="form-control" id="adminPassword2" placeholder="Password repeated" autocomplete="off">
                        </div>
                     </div>
                </form>
            </row>
            
        </div>

    </body>
</html>

