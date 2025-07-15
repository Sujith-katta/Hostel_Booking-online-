<?php
// Make sure includes/functions.php is included first, as it contains session_start()
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

// The session is already started by includes/functions.php, so remove session_start() here.
// session_start(); // <-- REMOVE THIS LINE (if it's on line 6 or elsewhere)

// Redirect if an admin is already logged in
if (isAdmin()) {
    header("Location: dashboard.php");
    exit();
}

$message = ""; // To store success/error messages for display

// --- Handle Admin Login ---
// In admin/index.php
if (isset($_POST['admin_login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $message = "Both username and password are required";
    } else {
        if (loginUser($conn, $username, $password, 'admin')) {
            $_SESSION['admin_authenticated'] = true; // Additional security flag
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Invalid admin credentials";
        }
    }
}

// --- Handle Admin Registration ---
if (isset($_POST['admin_register'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Basic server-side validation for registration
    if (empty($fullname) || empty($email) || empty($phone) || empty($username) || empty($password) || empty($confirm_password)) {
        $message = "<p class='error'>All registration fields are required.</p>";
    } elseif ($password !== $confirm_password) {
        $message = "<p class='error'>Passwords do not match.</p>";
    } elseif (strlen($password) < 6) { // Minimum password length
        $message = "<p class='error'>Password must be at least 6 characters long.</p>";
    } else {
        // Check if username or email already exists for an admin
        // Use prepared statements to prevent SQL injection
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND role = 'admin'");
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $message = "<p class='error'>Username or Email is already registered for an admin account.</p>";
        } else {
            // Register the new admin user
            if (registerUser($conn, $username, $password, 'admin', $fullname, $email, $phone)) {
                $message = "<p class='success'>Admin account created successfully! You can now log in.</p>";
                // Clear the POST data so registration fields don't pre-fill on refresh
                $_POST = array(); 
            } else {
                $message = "<p class='error'>Admin registration failed. Please try again.</p>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Access</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h2>Admin Login</h2>
        <?php 
        // Display any messages (success or error)
        echo $message; 
        ?>
        <form action="" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit" name="admin_login">Login</button>
        </form>

        <hr style="margin: 30px 0;"> <h2>New Admin? Register Here</h2>
        <form action="" method="POST">
            <label for="reg_fullname">Full Name:</label>
            <input type="text" id="reg_fullname" name="fullname" value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>" required>
            <label for="reg_email">Email:</label>
            <input type="email" id="reg_email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            <label for="reg_phone">Phone:</label>
            <input type="text" id="reg_phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
            <label for="reg_username">Username:</label>
            <input type="text" id="reg_username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            <label for="reg_password">Password:</label>
            <input type="password" id="reg_password" name="password" required>
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <button type="submit" name="admin_register">Register Admin Account</button>
        </form>
    </div>
</body>
</html>