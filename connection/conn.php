<?php
// Admin database connection
$admin_host = 'localhost';
$admin_user = 'root';
$admin_pass = '';
$admin_db = 'brgy_piggery';
$admin_conn = new mysqli($admin_host, $admin_user, $admin_pass, $admin_db);

if ($admin_conn->connect_error) {
    die("Admin Database Connection failed: " . $admin_conn->connect_error);
}

?>
