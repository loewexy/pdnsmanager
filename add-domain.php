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
    require_once 'lib/session.php';
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
        
        <script src="js/add-domain.js"></script>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-static-top">
            <div class="container">
                <div class="navbar-brand">
                    PDNS Manager
                </div>
                <ul class="nav navbar-nav">
                    <li><a href="domains.php">Domains</a></li>
                    <?php if($_SESSION['type'] == "admin") echo '<li><a href="users.php">Users</a></li>'; ?>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>
        
        <div class="container">
            
            <row>
                <h2 id="domain-name">Add Domain</h2>
            </row>
            
            <row>
                <form>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="zone-name" class="control-label">Name</label>
                            <input type="text" class="form-control" id="zone-name" placeholder="Primary" autocomplete="off" data-regex="^([^.]+\.)*[^.]+$" tabindex="1">
                        </div>
                        <div class="form-group">
                            <label for="zone-primary" class="control-label">Primary</label>
                            <input type="text" class="form-control" id="zone-primary" placeholder="Primary" autocomplete="off" data-regex="^([^.]+\.)*[^.]+$" tabindex="2">
                        </div>
                        <div class="form-group">
                            <label for="zone-mail" class="control-label">Email</label>
                            <input type="text" class="form-control" id="zone-mail" placeholder="Email" autocomplete="off" data-regex="^.+\@.+\.[^.]+$" tabindex="3">
                        </div>
                        <button id="zone-button-add" class="btn btn-primary" tabindex="8">Add</button>
                    </div>

                    <div class="col-md-2 col-md-offset-1">
                        <div class="form-group">
                            <label for="zone-refresh" class="control-label">Refresh</label>
                            <input type="text" class="form-control" id="zone-refresh" placeholder="Refresh" autocomplete="off" data-regex="^[0-9]+$" tabindex="4" value="3600">
                        </div>
                        <div class="form-group">
                            <label for="zone-retry" class="control-label">Retry</label>
                            <input type="text" class="form-control" id="zone-retry" placeholder="Retry" autocomplete="off" data-regex="^[0-9]+$" tabindex="5" value="900">
                        </div>
                    </div>

                    <div class="col-md-2 col-md-offset-1">
                        <div class="form-group">
                            <label for="zone-expire" class="control-label">Expire</label>
                            <input type="text" class="form-control" id="zone-expire" placeholder="Expire" autocomplete="off" data-regex="^[0-9]+$" tabindex="6" value="604800">
                        </div>
                        <div class="form-group">
                            <label for="zone-ttl" class="control-label">TTL</label>
                            <input type="text" class="form-control" id="zone-ttl" placeholder="TTL" autocomplete="off" data-regex="^[0-9]+$" tabindex="7" value="86400">
                        </div>
                    </div>
                </form>
            </row>
            
        </div>

    </body>
</html>

