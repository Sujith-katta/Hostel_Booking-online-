<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login/Register</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h2>Customer Login</h2>
        <form action="" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit" name="customer_login">Login</button>
        </form>

        <h2>New Customer? Register Here</h2>
        <form action="" method="POST">
            <label for="reg_fullname">Full Name:</label>
            <input type="text" id="reg_fullname" name="fullname" required>
            <label for="reg_email">Email:</label>
            <input type="email" id="reg_email" name="email" required>
            <label for="reg_phone">Phone:</label>
            <input type="text" id="reg_phone" name="phone" required>
            <label for="reg_username">Username:</label>
            <input type="text" id="reg_username" name="username" required>
            <label for="reg_password">Password:</label>
            <input type="password" id="reg_password" name="password" required>
            <button type="submit" name="customer_register">Register</button>
        </form>

        <?php
        include_once '../includes/db_connect.php';
        include_once '../includes/functions.php';

        if (isset($_POST['customer_login'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];

            if (loginUser($conn, $username, $password, 'customer')) {
                header("Location: dashboard.php");
                exit();
            } else {
                echo "<p class='error'>Invalid username or password.</p>";
            }
        }

        if (isset($_POST['customer_register'])) {
            $fullname = $_POST['fullname'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $username = $_POST['username'];
            $password = $_POST['password'];

            if (registerUser($conn, $username, $password, 'customer', $fullname, $email, $phone)) {
                echo "<p class='success'>Registration successful! You can now log in.</p>";
            } else {
                echo "<p class='error'>Registration failed. Username might already exist or an error occurred.</p>";
            }
        }
        ?>
    </div>
</body>
</html>