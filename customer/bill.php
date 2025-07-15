<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

if (!isCustomer()) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['booking_id'])) {
    header("Location: dashboard.php");
    exit();
}

$booking_id = $_GET['booking_id'];

// Fetch all necessary details for the bill
$stmt = $conn->prepare("
    SELECT 
        b.id AS booking_id, 
        b.total_amount, 
        b.booking_date, 
        b.payment_id,
        u.fullname, 
        u.email, 
        u.phone,
        rt.name AS room_name, 
        rt.base_price, 
        rt.laundry_fee, 
        rt.mess_veg_fee, 
        rt.mess_nonveg_fee,
        b.mess_type
    FROM 
        bookings b
    JOIN 
        users u ON b.user_id = u.id
    JOIN 
        room_types rt ON b.room_type_id = rt.id
    WHERE 
        b.id = ? AND b.user_id = ? AND b.status = 'paid'
");
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$bill_details = $result->fetch_assoc();

if (!$bill_details) {
    echo "<p class='error'>Bill not found or payment not confirmed for this booking.</p>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Booking Bill</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .bill-container {
            border: 1px solid #ddd;
            padding: 20px;
            max-width: 600px;
            margin: 20px auto;
            background-color: #f9f9f9;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .bill-header, .bill-footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .bill-details p {
            margin: 5px 0;
        }
        .bill-items table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .bill-items th, .bill-items td {
            border: 1px solid #eee;
            padding: 8px;
            text-align: left;
        }
        .bill-total {
            text-align: right;
            margin-top: 20px;
            font-size: 1.2em;
            font-weight: bold;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                box-shadow: none;
                border: none;
                margin: 0;
            }
            button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container bill-container">
        <div class="bill-header">
            <h1>Hostel Booking Bill</h1>
            <p>Date: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>

        <div class="bill-details">
            <h3>Customer Details:</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($bill_details['fullname']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($bill_details['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($bill_details['phone']); ?></p>
            <p><strong>Booking ID:</strong> <?php echo $bill_details['booking_id']; ?></p>
            <p><strong>Booking Date:</strong> <?php echo date('Y-m-d H:i', strtotime($bill_details['booking_date'])); ?></p>
            <p><strong>Payment ID:</strong> <?php echo htmlspecialchars($bill_details['payment_id']); ?></p>
        </div>

        <div class="bill-items">
            <h3>Booking Summary:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="text-align: right;">Amount (INR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo htmlspecialchars($bill_details['room_name']); ?> (Base Rent)</td>
                        <td style="text-align: right;">₹<?php echo number_format($bill_details['base_price'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Laundry Fee</td>
                        <td style="text-align: right;">₹<?php echo number_format($bill_details['laundry_fee'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Mess (<?php echo ucfirst($bill_details['mess_type']); ?>)</td>
                        <td style="text-align: right;">₹<?php echo number_format(($bill_details['mess_type'] === 'veg' ? $bill_details['mess_veg_fee'] : $bill_details['mess_nonveg_fee']), 2); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="bill-total">
            <p>Total Amount Paid: **₹<?php echo number_format($bill_details['total_amount'], 2); ?>**</p>
        </div>

        <div class="bill-footer">
            <p>Thank you for booking with us!</p>
            <button onclick="window.print()">Print Bill</button>
            <p><a href="dashboard.php">Back to Dashboard</a></p>
        </div>
    </div>
</body>
</html>