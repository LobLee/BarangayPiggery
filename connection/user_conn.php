<?php

// Owner database connection
$owner_host = 'localhost';
$owner_user = 'root';
$owner_pass = '';
$owner_db = 'brgy_user_piggery';
$owner_conn = new mysqli($owner_host, $owner_user, $owner_pass, $owner_db);


if ($owner_conn->connect_error) {
    die("Owner Database Connection failed: " . $owner_conn->connect_error);
}

?>
