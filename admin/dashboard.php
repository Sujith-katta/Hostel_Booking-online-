<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Strict authentication check
if (!isAdmin()) {
    $_SESSION['access_denied'] = "You must login as admin to access this page";
    header("Location: index.php");
    exit();
}

// Rest of your page code...

$success_message = "";
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <?php if (!empty($success_message)): ?>
            <p class="success"><?php echo $success_message; ?></p>
        <?php endif; ?>

        <h2>Welcome, Admin <?php echo $_SESSION['username']; ?>!</h2>
        <nav>
            <ul>
                <li><a href="manage_rooms.php">Manage Room Types</a></li>
                <li><a href="manage_bookings.php">Manage Bookings</a></li>
                <li><a href="upload_upi.php">Upload UPI QR Code</a></li>
                <li><a href="../includes/logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</body>
</html>
