<?php 
include ('../connection/conn.php'); // Main database connection
include ('../sidebar/owner_sidebar.php');
include ('../connection/user_conn.php'); // Owner-specific connection

// Query to get the count of health monitoring records
$health_monitoring_query = "SELECT COUNT(*) AS total_health_monitoring FROM health_monitoring";
$health_monitoring_result = $owner_conn->query($health_monitoring_query);
$health_monitoring_count = $health_monitoring_result->fetch_assoc()['total_health_monitoring'];

// Query to get the count of piggeries
$piggeries_query = "SELECT COUNT(*) AS total_piggeries FROM piggeries";
$piggeries_result = $owner_conn->query($piggeries_query);
$piggeries_count = $piggeries_result->fetch_assoc()['total_piggeries'];

// Query to get the count of incident reports
$incident_reports_query = "SELECT COUNT(*) AS total_incident_reports FROM incident_reports";
$incident_reports_result = $owner_conn->query($incident_reports_query);
$incident_reports_count = $incident_reports_result->fetch_assoc()['total_incident_reports'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Barangay Piggery Monitoring System - Owner Dashboard</title>

    <!-- Custom fonts for this template-->
    <link href="../template/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="../template/css/sb-admin-2.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="text-center">Welcome to Owner's Dashboard</h1><br>

    <!-- Content Row -->
    <div class="row">
        <!-- Health Monitoring Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="health_monitoring.php" style="text-decoration: none;">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Health Monitoring</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $health_monitoring_count; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-heartbeat fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Piggeries Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="piggery_info.php" style="text-decoration: none;">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Piggeries</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $piggeries_count; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-piggy-bank fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Incident Reports Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="incident_report.php" style="text-decoration: none;">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Incident Reports</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $incident_reports_count; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
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
