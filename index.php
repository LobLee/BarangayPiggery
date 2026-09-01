<?php
include('connection/conn.php');

// Start session
session_start();

// Initialize error message
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear the message after displaying it
} else {
    $error_message = '';
}



// Login process
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $username = mysqli_real_escape_string($admin_conn, $_POST['username']);
    $password = mysqli_real_escape_string($admin_conn, $_POST['password']);
    $remember = isset($_POST['remember']); // Check if "Remember me" is selected

    // Query the admin database initially, assuming all users are stored in `registered_users` table
    $query = "SELECT * FROM registered_users WHERE username='$username'";
    $result = $admin_conn->query($query);

    // Verify if query was successful and user was found
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password using password_verify()
        if (password_verify($password, $user['password'])) {
            // Set session variables for username and role
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['user_id']; // Assuming `user_id` is the column name in `registered_users`

            // Set cookies for "Remember me" feature
            if ($remember) {
                setcookie("username", $user['username'], time() + (86400 * 30), "/"); // 30 days
                setcookie("user_id", $user['user_id'], time() + (86400 * 30), "/");
            } else {
                // Clear the cookies if "Remember me" is unchecked
                setcookie("username", "", time() - 3600, "/");
                setcookie("user_id", "", time() - 3600, "/");
            }

            // Log the login activity
            $user_id = $user['user_id'];
            $action_type = 'Login';
            $activity_description = "User logged in successfully.";
            $module = 'Authentication';

            $log_query = "INSERT INTO activity_logs (user_id, action_type, activity_description, module) 
                          VALUES ('$user_id', '$action_type', '$activity_description', '$module')";
            $admin_conn->query($log_query);

            // Redirect based on user role
            if ($user['role'] === 'admin') {
                header("Location: Admin/admin_dashboard.php?user_id=" . $user['user_id']);
            } elseif ($user['role'] === 'owner') {
                header("Location: Owner/owner_dashboard.php?user_id=" . $user['user_id']);
            } elseif ($user['role'] === 'inspector') {
                header("Location: Inspector/inspector_dashboard.php?user_id=" . $user['user_id']);
            }
            exit(); // Ensure no further code is executed after redirection
        } else {
            // Incorrect password
            $_SESSION['error_message'] = "Incorrect password. Please try again.";
            header("Location: index.php"); // Redirect to the same page to show the error
            exit();
        }
    } else {
        // No user found
        $_SESSION['error_message'] = "Incorrect Username.";
        header("Location: index.php"); // Redirect to the same page to show the error
        exit();
    }
}

// Check for cookies and auto-login if they exist
if (!isset($_SESSION['username']) && isset($_COOKIE['username']) && isset($_COOKIE['user_id'])) {
    $_SESSION['username'] = $_COOKIE['username'];
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    header("Location: index.php"); // Redirect to login page or default dashboard
    exit();
}

// Retrieve the username from the cookie if it exists
$remembered_username = isset($_COOKIE['username']) ? $_COOKIE['username'] : '';
?>


<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="include/fonts/icomoon/style.css">
  <link rel="stylesheet" href="include/css/owl.carousel.min.css">
  <link rel="stylesheet" href="include/css/bootstrap.min.css">
  <link rel="stylesheet" href="include/css/style.css">
  <title>Barangay Piggery Monitoring System</title>

  <style>
    /* Apply background image to the entire body */
    body {
      background-image: url('include/images/piggery.png'); /* Replace with your actual image path */
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      background-attachment: fixed;
     
    }

    .contents {
      background-color: rgba(255, 255, 255, 0.3); /* Semi-transparent white background */
      padding: 2rem;
      border-radius: 10px;
      width: 50px;
      max-width: 500px;
      box-shadow: 0 4px 8px rgba(2, 2, 2, 2); /* Optional shadow */
    }

    .input-group-text {
      background: none;
      border: none;
    }

    .error-message {
      color: red;
      font-weight: bold;
      margin-bottom: 10px;
    }
    .input-group input::placeholder {
      color: black;
    }
  </style>
</head>

<body>
<div class="content">
  <div class="container">
    <div class="row justify-content-center align-items-center">
      <div class="col-md-6 contents">
        <div class="row justify-content-center">
          <div class="col-md-8">
            <div class="mb-4 text-center">
              <h1 style="font-weight: bolder;">Log In</h1>
              <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
              <?php endif; ?>
            </div>
            <form action="index.php" method="post">
              <div class="form-group first" style="background-color: rgba(255, 255, 255, 0.4) !important; box-shadow: 0 4px 8px rgba(2, 2, 2, 2); border-radius: 10px">
                <div class="input-group" >
                  <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?php echo htmlspecialchars($remembered_username); ?>">
                </div>
              </div><br>
              <div class="form-group last mb-4" style="background-color: rgba(255, 255, 255, 0.4) !important; box-shadow: 0 4px 8px rgba(2, 2, 2, 2); border-radius: 10px">
                <div class="input-group" >
                  <input type="password" class="form-control"  id="password" name="password" placeholder="Password" onfocus="toggleEyeIconVisibility()" oninput="toggleEyeIconVisibility()">
                  <span class="input-group-text" onclick="togglePassword()" style="cursor: pointer;" id="eyeIconContainer">
                    <i class="fas fa-eye-slash" id="togglePasswordIcon"></i>
                  </span>
                </div>
              </div>

              <div class="d-flex mb-5 align-items-center">
                <label class="control control--checkbox mb-0">
                  <span class="caption" style="color: black; font-size: medium">Remember me</span>
                  <input type="checkbox" name="remember" <?php echo $remembered_username ? 'checked' : ''; ?> />
                  <div class="control__indicator"></div>
                </label>
                <span class="ml-auto"><a href="forgot_password.php" class="forgot-pass" style="color:blue; font-size: medium";> Forgot Password</a></span>
              </div>
              <input type="submit" value="Login" class="btn btn-block btn-primary" style="font-weight: bold; margin-top: -20px">
              <div class="text-center mt-2">
                <label for="register"> Don’t have an account? </label> <a href="register.php" class="text-decoration" style="color: blue" >Register Here.</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function togglePassword() {
    var passwordField = document.getElementById("password");
    var toggleIcon = document.getElementById("togglePasswordIcon");

    if (passwordField.type === "password") {
      passwordField.type = "text";
      toggleIcon.classList.remove("fa-eye-slash");
      toggleIcon.classList.add("fa-eye");
    } else {
      passwordField.type = "password";
      toggleIcon.classList.remove("fa-eye");
      toggleIcon.classList.add("fa-eye-slash");
    }
  }

  function toggleEyeIconVisibility() {
    var passwordField = document.getElementById("password");
    var eyeIconContainer = document.getElementById("eyeIconContainer");

    if (passwordField.value !== "" || document.activeElement === passwordField) {
      eyeIconContainer.style.visibility = "visible";
    } else {
      eyeIconContainer.style.visibility = "hidden";
    }
  }

  document.addEventListener("DOMContentLoaded", function() {
    toggleEyeIconVisibility();
  });
</script>

<script src="include/js/jquery-3.3.1.min.js"></script>
<script src="include/js/popper.min.js"></script>
<script src="include/js/bootstrap.min.js"></script>
<script src="include/js/main.js"></script>
</body>
</html>
