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
        
        <script src="js/edit-master.js"></script>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-static-top">
            <div class="container">
                <div class="navbar-brand">
                    PDNS Manager
                </div>
                <ul class="nav navbar-nav">
                    <li><a href="domains.php">Domains</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>
        
        <div class="container">
            
            <row>
                <h2 id="domain-name">example.com</h2>
            </row>
            
            <div id="soa" class="container">
                <row>
                    <h3>SOA</h3>
                </row>
                <row>
                    <form>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="soa-primary" class="control-label">Primary</label>
                                <input type="text" class="form-control" id="soa-primary" placeholder="Primary" autocomplete="off" data-regex="^([^.]+\.)*[^.]+$" tabindex="1">
                            </div>
                            <div class="form-group">
                                <label for="soa-mail" class="control-label">Email</label>
                                <input type="text" class="form-control" id="soa-mail" placeholder="Email" autocomplete="off" data-regex="^.+\@.+\.[^.]+$" tabindex="2">
                            </div>
                            <button type="submit" class="btn btn-primary" tabindex="7">Save</button>
                        </div>
                        
                        <div class="col-md-2 col-md-offset-1">
                            <div class="form-group">
                                <label for="soa-refresh" class="control-label">Refresh</label>
                                <input type="text" class="form-control" id="soa-refresh" placeholder="Refresh" autocomplete="off" data-regex="^[0-9]+$" tabindex="3">
                            </div>
                            <div class="form-group">
                                <label for="soa-retry" class="control-label">Retry</label>
                                <input type="text" class="form-control" id="soa-retry" placeholder="Retry" autocomplete="off" data-regex="^[0-9]+$" tabindex="4">
                            </div>
                        </div>
                        
                        <div class="col-md-2 col-md-offset-1">
                            <div class="form-group">
                                <label for="soa-expire" class="control-label">Expire</label>
                                <input type="text" class="form-control" id="soa-expire" placeholder="Expire" autocomplete="off" data-regex="^[0-9]+$" tabindex="5">
                            </div>
                            <div class="form-group">
                                <label for="soa-ttl" class="control-label">TTL</label>
                                <input type="text" class="form-control" id="soa-ttl" placeholder="TTL" autocomplete="off" data-regex="^[0-9]+$" tabindex="6">
                            </div>
                        </div>

                        <div class="col-md-2 col-md-offset-1">
                            <div class="form-group">
                                <label for="soa-serial" class="control-label">Serial</label>
                                <input type="text" class="form-control" id="soa-serial" placeholder="Serial" disabled data-regex=".*">
                            </div>
                        </div>
                    </form>
                </row>
            </div>
            
            <row class="row vspacer-50"></row>
            
            <div id="records" class="container">
                <table class="table table-hover" id="table-records">
                    <thead>
                        <tr>
                            <td class="cell-vertical-middle"><strong>ID</strong> <span class="glyphicon glyphicon-sort cursor-pointer"></span></td>
                            <td class="cell-vertical-middle">
                                <form class="form-inline">
                                    <div class="form-group">
                                    <strong>Name</strong> 
                                    <span class="glyphicon glyphicon-sort cursor-pointer "></span>
                                    <input type="text" class="form-control no-shadow" id="searchName" placeholder="Search" autocomplete="off">
                                    </div>
                                </form>
                            </td>
                            <td class="cell-vertical-middle">
                                <form class="form-inline">
                                    <div class="form-group">
                                    <strong>Type</strong> 
                                    <span class="glyphicon glyphicon-sort cursor-pointer"></span>
                                    <select class="form-control no-shadow" id="searchType" multiple>
                                        <option value="A" >A</option>
                                        <option value="AAAA" >AAAA</option>
                                        <option value="AFSDB" >AFSDB</option>
                                        <option value="CERT" >CERT</option>
                                        <option value="CNAME" >CNAME</option>
                                        <option value="DHCID" >DHCID</option>
                                        <option value="DLV" >DLV</option>
                                        <option value="DNSKEY" >DNSKEY</option>
                                        <option value="DS" >DS</option>
                                        <option value="EUI48" >EUI48</option>
                                        <option value="EUI64" >EUI64</option>
                                        <option value="HINFO" >HINFO</option>
                                        <option value="IPSECKEY" >IPSECKEY</option>
                                        <option value="KEY" >KEY</option>
                                        <option value="KX" >KX</option>
                                        <option value="LOC" >LOC</option>
                                        <option value="MINFO" >MINFO</option>
                                        <option value="MR" >MR</option>
                                        <option value="MX" >MX</option>
                                        <option value="NAPTR" >NAPTR</option>
                                        <option value="NS" >NS</option>
                                        <option value="NSEC" >NSEC</option>
                                        <option value="NSEC3" >NSEC3</option>
                                        <option value="NSEC3PARAM" >NSEC3PARAM</option>
                                        <option value="OPT" >OPT</option>
                                        <option value="PTR" >PTR</option>
                                        <option value="RKEY" >RKEY</option>
                                        <option value="RP" >RP</option>
                                        <option value="RRSIG" >RRSIG</option>
                                        <option value="SOA" >SOA</option>
                                        <option value="SPF" >SPF</option>
                                        <option value="SRV" >SRV</option>
                                        <option value="SSHFP" >SSHFP</option>
                                        <option value="TLSA" >TLSA</option>
                                        <option value="TSIG" >TSIG</option>
                                        <option value="TXT" >TXT</option>
                                        <option value="WKS" >WKS</option>
                                    </select>
                                    </div>
                                </form>
                            </td>
                            <td class="cell-vertical-middle">
                                <form class="form-inline">
                                    <div class="form-group">
                                    <strong>Content</strong> 
                                    <span class="glyphicon glyphicon-sort cursor-pointer"></span>
                                    <input type="text" class="form-control no-shadow" id="searchContent" placeholder="Search" autocomplete="off">
                                    </div>
                                </form>
                            </td>
                            <td class="cell-vertical-middle"><strong>Priority</strong> <span class="glyphicon glyphicon-sort cursor-pointer"></span></td>
                            <td class="cell-vertical-middle"><strong>TTL</strong> <span class="glyphicon glyphicon-sort cursor-pointer"></span></td>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>

                </table>
            </div>
        </div>

    </body>
</html>

