<?php

if(isset($_POST['action']) && !empty($_POST['action'])) {
    $action = $_POST['action'];
    switch($action) {
        case 'failedlogin' :
	    #error_log("login failed for user ".$_POST['user']." (".$_SERVER['REMOTE_ADDR']), 3, "/tmp/mes-erreurs.log");
	    error_log("PDNS-MANAGER : login failed for user ".$_POST['user']." (".$_SERVER['REMOTE_ADDR'].")\n", 3, "/var/log/apache2/error.log");
	break;
    }
}
?>
