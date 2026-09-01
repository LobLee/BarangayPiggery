<?php
// Start session and include database connection
include('../connection/conn.php');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user_id is set in the session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

$user_id = $_SESSION['user_id']; // Get the user_id from the session
$notifications = [];

// Fetch user information
$query_user = "SELECT first_name, middle_name, last_name, profile_image 
               FROM registered_users 
               WHERE user_id = ?";
$stmt_user = $admin_conn->prepare($query_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

$user = $result_user->fetch_assoc() ?? [
    'first_name' => 'User',
    'last_name' => '',
    'profile_image' => '../Owner/image/default.png' // Default image
];

// Fetch notifications for the user
$query_notifications = "SELECT id, title, message, module, created_at, is_read 
                        FROM brgy_piggery.notifications 
                        WHERE owner_id = ? 
                        ORDER BY created_at DESC";
$stmt_notifications = $admin_conn->prepare($query_notifications);
$stmt_notifications->bind_param("i", $user_id);
$stmt_notifications->execute();
$result_notifications = $stmt_notifications->get_result();

while ($row = $result_notifications->fetch_assoc()) {
    $notifications[] = $row;
}

// Count unread notifications
$unread_count = count(array_filter($notifications, fn($n) => $n['is_read'] == 0));

?>



<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Barangay Piggery Monitoring System</title>

    <!-- Custom fonts for this template-->
    <link href="../template/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="../template/css/sb-admin-2.min.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-piggy-bank" style="margin-top: 60px;"></i>
                </div>

                <div class="sidebar-brand-text mx-3" style="margin-top: 60px;">Barangay Piggery Monitoring System</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-5">
            <div class="sidebar-heading" style="margin-top: -30px;">
                Owner
            </div>
            <!-- Nav Item - Dashboard -->

            <li class="nav-item">
                <a class="nav-link" href="owner_dashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="piggery_info.php">
                    <i class="fas fa-fw fa-clipboard-list"></i> <!-- Changed to clipboard list for piggery management -->
                    <span>Piggery Management</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="health_monitoring.php">
                    <i class="fas fa-fw fa-heartbeat"></i> <!-- Changed to heartbeat for health monitoring -->
                    <span>Health Monitoring</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="incident_report.php">
                    <i class="fas fa-fw fa-exclamation-triangle"></i> <!-- Changed to exclamation triangle for incident reporting -->
                    <span>Incident Reporting</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>

        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                                aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small"
                                            placeholder="Search for..." aria-label="Search"
                                            aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <!-- Notifications -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <?php if ($unread_count > 0): ?>
                                    <span class="badge badge-danger badge-counter"><?= $unread_count ?></span>
                                <?php endif; ?>
                            </a>
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">Alerts Center</h6>
                                <?php if (!empty($notifications)): ?>
                                    <?php foreach ($notifications as $notification): ?>
                                        <a class="dropdown-item d-flex align-items-center" href="read_notifications.php?id=<?= $notification['id'] ?>">
                                            <div class="mr-3">
                                                <div class="icon-circle bg-<?= $notification['is_read'] ? 'secondary' : 'primary' ?>">
                                                    <i class="fas fa-info-circle text-white"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="small text-gray-500"><?= date('F d, Y', strtotime($notification['created_at'])) ?></div>
                                                <span class="font-weight-bold"><?= htmlspecialchars($notification['title']) ?></span>
                                                <div><?= htmlspecialchars($notification['message']) ?> (<?= htmlspecialchars($notification['module']) ?>)</div>
                                            </div>
                                        </a>

                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <a class="dropdown-item text-center small text-gray-500">No new alerts</a>
                                <?php endif; ?>
                                <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
                            </div>
                        </li>


                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                </span>
                                <img class="img-profile rounded-circle"
                                    src="<?php echo htmlspecialchars($user['profile_image']); ?>" width="15%" height="15%">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="edit_owner_profile.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="settings.php">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>


                <!-- Scroll to Top Button-->
                <a class="scroll-to-top rounded" href="#page-top">
                    <i class="fas fa-angle-up"></i>
                </a>

                <!-- Logout Modal-->
                <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                                <a class="btn btn-primary" href="../index.php">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bootstrap core JavaScript-->
                <script src="../template/vendor/jquery/jquery.min.js"></script>
                <script src="../template/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

                <!-- Core plugin JavaScript-->
                <script src="../template/vendor/jquery-easing/jquery.easing.min.js"></script>

                <!-- Custom scripts for all pages-->
                <script src="../template/js/sb-admin-2.min.js"></script>

</body>

</html>