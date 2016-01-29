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
?>
<html>
    <head>
        <title>PDNS Manager - Domains</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <link href="include/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="include/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
        <link href="include/select2/select2.min.css" rel="stylesheet">
        <link href="include/select2/select2-bootstrap.min.css" rel="stylesheet">
        <link href="include/custom.css" rel="stylesheet">
        
        <script src="include/jquery.js"></script>
        <script src="include/bootstrap/js/bootstrap.min.js"></script>
        <script src="include/select2/select2.min.js"></script>
        
        <script src="js/edit-user.js"></script>
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
                    <li><a href="password.php">Password</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>
        
        <div class="container">
            
            <row>
                <h2 id="heading">Change user</h2>
            </row>
            
            <row>
                <div class="col-md-3">
                    <form>
                    
                        <div class="form-group">
                            <label for="user-name" class="control-label">Name</label>
                            <input type="text" class="form-control" id="user-name" placeholder="Username" autocomplete="off" data-regex="^[A-Za-z0-9\._-]+$" tabindex="1">
                        </div>
                        <div class="form-group">
                            <label for="user-password" class="control-label">Password</label>
                            <input type="password" class="form-control" id="user-password" placeholder="(Unchanged)" autocomplete="off" tabindex="2">
                        </div>
                        <div class="form-group">
                            <label for="user-password2" class="control-label">Password repeated</label>
                            <input type="password" class="form-control" id="user-password2" placeholder="(Unchanged)" autocomplete="off" tabindex="3">
                        </div>
                        <div class="form-group">
                            <label for="user-type" class="control-label">Type</label>
                            <select id="user-type" class="form-control" tabindex="4">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <button id="user-button-add" class="btn btn-primary" tabindex="5">Change</button>
                    </form>
                </div>
                
                <div class="col-md-3 col-md-offset-1 defaulthidden" id="permissions">
                        <h3>Permissions</h3>
                        
                        <table class="table table-hover">
                            <tbody>
                            </tbody>
                        </table>
                        
                        <label for="selectAdd" class="control-label">Add</label>
                        <select multiple class="form-control" id="selectAdd"></select>
                        <div class="vspacer-15"></div>
                        <button class="btn btn-primary" id="btnAddPermissions">Add</button>
                </div>
            </row>
            
        </div>
        <?php echo '<span class="hidden" id="csrfToken">' . $_SESSION['csrfToken'] . '</span>'; ?> 

    </body>
</html>

