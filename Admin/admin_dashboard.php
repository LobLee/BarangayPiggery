<?php
include ('../connection/conn.php'); // Main database connection
include ('../sidebar/admin_sidebar.php');

// Query to get the count of health management records
$manage_health_query = "SELECT COUNT(*) AS total_manage_health FROM manage_health";
$manage_health_result = $admin_conn->query($manage_health_query);
$manage_health_count = $manage_health_result->fetch_assoc()['total_manage_health'];

// Query to get the count of incident management records
$manage_incidents_query = "SELECT COUNT(*) AS total_manage_incidents FROM manage_incidents";
$manage_incidents_result = $admin_conn->query($manage_incidents_query);
$manage_incidents_count = $manage_incidents_result->fetch_assoc()['total_manage_incidents'];

// Query to get the count of piggery management records
$manage_piggery_query = "SELECT COUNT(*) AS total_manage_piggery FROM manage_piggery";
$manage_piggery_result = $admin_conn->query($manage_piggery_query);
$manage_piggery_count = $manage_piggery_result->fetch_assoc()['total_manage_piggery'];

// Query to get the count of registered users
$registered_users_query = "SELECT COUNT(*) AS total_registered_users FROM registered_users";
$registered_users_result = $admin_conn->query($registered_users_query);
$registered_users_count = $registered_users_result->fetch_assoc()['total_registered_users'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Barangay Piggery Monitoring System - Admin Dashboard</title>

    <!-- Custom fonts for this template-->
    <link href="../template/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="../template/css/sb-admin-2.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="text-center">Welcome to Admin's Dashboard</h1><br>

    <!-- Content Row -->
    <div class="row">
        <!-- Manage Health Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="manage_health.php" style="text-decoration: none;">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Manage Health</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $manage_health_count; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-heartbeat fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Manage Incidents Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="manage_incident.php" style="text-decoration: none;">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Manage Incidents</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $manage_incidents_count; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Manage Piggery Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="manage_piggery.php" style="text-decoration: none;">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Manage Piggery</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $manage_piggery_count; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-piggy-bank fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Registered Users Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="user_management.php" style="text-decoration: none;">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Registered Users</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $registered_users_count; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<?php include('../footer.php') ?>

</body>
</html>
