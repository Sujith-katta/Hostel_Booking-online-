<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Booking System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Welcome to the Hostel Booking System!</h2>
        <p>Please select your role to proceed:</p>
        <div style="text-align: center; margin-top: 30px;">
            <a href="admin/index.php" class="button">Admin Login</a>
            <a href="customer/index.php" class="button">Customer Login / Register</a>
        </div>
    </div>
    <style>
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 18px;
            margin: 0 10px;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</body>
</html>