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
        <title>PDNS Manager - Remotes</title>
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
        
        <script src="js/edit-remote.js"></script>
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
                <h2 id="heading">Remote access</h2>
            </row>
            
            <row>
                <div class="col-md-4">
                    <table id="permissions" class="table table-hover">
                        <thead>
                            <tr>    
                                <td><strong>ID</strong></td>
                                <td><strong>Description</strong></td>
                                <td><strong>Type</strong></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    
                    <row>
                        <button id="button-add-password" class="btn btn-success">Add password</button>
                        <button id="button-add-key" class="btn btn-success">Add key</button>
                    </row>
                </div>
                <div class="col-md-4 col-md-offset-1" id="info-dialogs">
                    <row id="data-password" class="defaulthidden">
                        <form>
                            <div class="form-group">
                                <label for="data-password-description" class="control-label">Description</label>
                                <input type="text" class="form-control" id="data-password-description" placeholder="Description" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label for="data-password-password" class="control-label">Password</label>
                                <input type="password" class="form-control" id="data-password-password" placeholder="Password" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label for="data-password-password2" class="control-label">Password repeated</label>
                                <input type="password" class="form-control" id="data-password-password2" placeholder="Password repeated" autocomplete="off">
                            </div>
                            <button id="data-password-confirm" class="btn btn-primary">Add</button>
                            <button id="data-password-cancel" class="btn btn-default">Cancel</button>
                        </form>
                    </row>
                    
                    <row id="data-key" class="defaulthidden">
                        <form>
                            <div class="form-group">
                                <label for="data-key-description" class="control-label">Description</label>
                                <input type="text" class="form-control" id="data-key-description" placeholder="Description" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label for="data-key-key" class="control-label">RSA Public Key</label>
                                <textarea class="form-control" id="data-key-key" placeholder="Enter RSA Public Key" autocomplete="off" cols="100" rows="10"></textarea>
                            </div>
                            <button id="data-key-confirm" class="btn btn-primary">Add</button>
                            <button id="data-key-cancel" class="btn btn-default">Cancel</button>
                        </form>
                    </row>
                </div>
            </row>
        </div>
        <?php echo '<span class="hidden" id="csrfToken">' . $_SESSION['csrfToken'] . '</span>'; ?> 

    </body>
</html>

