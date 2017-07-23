<?php
    require_once '../lib/headers.php';
    session_start();
    session_destroy();
    setcookie("authSecret", "", 1, "/", "", false, true);
    echo('{}');
?>

