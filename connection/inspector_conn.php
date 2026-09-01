<?php


// Inspector database connection
$inspector_host = 'localhost';
$inspector_user = 'root';
$inspector_pass = '';
$inspector_db = 'brgy_inspector_piggery';
$inspector_conn = new mysqli($inspector_host, $inspector_user, $inspector_pass, $inspector_db);

if ($inspector_conn->connect_error) {
    die("Inspector Database Connection failed: " . $inspector_conn->connect_error);
}
?>
