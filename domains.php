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
        <link href="include/select2/select2.min.css" rel="stylesheet">
        <link href="include/select2/select2-bootstrap.min.css" rel="stylesheet">
        <link href="include/custom.css" rel="stylesheet">
        
        <script src="include/jquery.js"></script>
        <script src="include/bootstrap/js/bootstrap.min.js"></script>
        <script src="include/select2/select2.min.js"></script>
        
        <script src="js/domains.js"></script>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-static-top">
            <div class="container">
                <div class="navbar-brand">
                    PDNS Manager
                </div>
                <ul class="nav navbar-nav">
                    <li class="active"><a href="domains.php">Domains</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>
        
        <div class="container">
            <table class="table table-hover" id="table-domains">
                <thead>
                    <tr>
                        <td class="cell-vertical-middle"><strong>ID</strong> <span class="glyphicon glyphicon-sort cursor-pointer"></span></td>
                        <td class="cell-vertical-middle">
                            <form class="form-inline">
                                <div class="form-group">
                                <strong>Name</strong> 
                                <span class="glyphicon glyphicon-sort cursor-pointer cursor-pointer"></span>
                                <input type="text" class="form-control no-shadow" id="searchName" placeholder="Search" autocomplete="off">
                                </div>
                            </form>
                        </td>
                        <td class="cell-vertical-middle">
                            <form class="form-inline">
                                <div class="form-group">
                                <strong>Type</strong> 
                                <span class="glyphicon glyphicon-sort cursor-pointer cursor-pointer"></span>
                                <select class="form-control no-shadow" id="searchType">
                                    <option value="none">No filter...</option>
                                    <option value="MASTER">MASTER</option>
                                </select>
                                </div>
                            </form>
                        </td>
                        <td class="cell-vertical-middle"><strong>Records</strong> <span class="glyphicon glyphicon-sort cursor-pointer"></span></td>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                
            </table>
        </div>

    </body>
</html>
