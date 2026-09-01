<?php 
include ('../sidebar/inspector_sidebar.php');
include ('../connection/conn.php'); // Database connection
include ('../connection/inspector_conn.php'); // Database connection

// Query to get the count of piggeries
$piggeries_query = "SELECT COUNT(*) AS total_piggeries FROM inspect_piggeries";
$piggeries_result = $inspector_conn->query($piggeries_query);
$piggeries_count = $piggeries_result->fetch_assoc()['total_piggeries'];

// Query to get the count of incident actions
$incident_actions_query = "SELECT COUNT(*) AS total_incidents FROM incident_actions";
$incident_actions_result = $inspector_conn->query($incident_actions_query);
$incident_actions_count = $incident_actions_result->fetch_assoc()['total_incidents'];

// Query to get the count of inspections
$inspections_query = "SELECT COUNT(*) AS total_inspections FROM inspection_results";
$inspections_result = $inspector_conn->query($inspections_query);
$inspections_count = $inspections_result->fetch_assoc()['total_inspections'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Barangay Piggery Monitoring System - Inspector Dashboard</title>

    <!-- Custom fonts for this template-->
    <link href="../template/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="../template/css/sb-admin-2.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="text-center">Inspector Dashboard</h1>

    <!-- Content Row -->
    <div class="row">
        <!-- Piggeries Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="inspect_piggery.php" style="text-decoration: none;">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Piggeries</div>
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

        <!-- Incident Actions Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="incident_actions.php" style="text-decoration: none;">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Incident Actions</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $incident_actions_count; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Inspections Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="results.php" style="text-decoration: none;">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Inspections</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $inspections_count; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
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
