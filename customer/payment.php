
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

// Fetch booking details
$stmt = $conn->prepare("SELECT b.total_amount, b.status, b.payment_id, rt.name AS room_name 
                        FROM bookings b 
                        JOIN room_types rt ON b.room_type_id = rt.id 
                        WHERE b.id = ? AND b.user_id = ?");
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$booking_details = $result->fetch_assoc();

if (!$booking_details) {
    echo "<p class='error'>Invalid booking ID.</p>";
    exit();
}

// Check if payment already submitted
if ($booking_details['payment_id'] && $booking_details['status'] === 'pending') {
    echo "<p class='success'>Payment already submitted. Transaction ID: " . 
         htmlspecialchars($booking_details['payment_id']) . 
         ". Awaiting admin verification.</p>";
    echo "<p><a href='dashboard.php'>Back to Dashboard</a></p>";
    exit();
}

// Fetch UPI details
$upi_details = $conn->query("SELECT * FROM upi_details LIMIT 1")->fetch_assoc();

// Handle payment confirmation
if (isset($_POST['confirm_payment'])) {
    $payment_id = trim($_POST['payment_id']);

    if (empty($payment_id)) {
        echo "<p class='error'>Transaction ID is required.</p>";
    } else {
        $stmt_update = $conn->prepare("UPDATE bookings SET payment_id = ? WHERE id = ?");
        $stmt_update->bind_param("si", $payment_id, $booking_id);
        
        if ($stmt_update->execute()) {
            $_SESSION['success_message'] = "Payment details submitted successfully! Admin will verify your payment.";
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<p class='error'>Error submitting payment details.</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Payment</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h2>Make Payment for Booking #<?php echo $booking_id; ?></h2>
        
        <div class="booking-info">
            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($booking_details['room_name']); ?></p>
            <p><strong>Total Amount:</strong> ₹<?php echo number_format($booking_details['total_amount'], 2); ?></p>
            <p><strong>Status:</strong> <?php echo ucfirst($booking_details['status']); ?></p>
        </div>

        <?php if ($upi_details && file_exists($upi_details['qr_code_path'])): ?>
            <div class="payment-instructions">
                <h3>Payment Instructions</h3>
                <ol>
                    <li>Send payment of ₹<?php echo number_format($booking_details['total_amount'], 2); ?> using:</li>
                    <li class="upi-details">
                        <img src="<?php echo htmlspecialchars($upi_details['qr_code_path']); ?>" alt="UPI QR Code">
                        <p>UPI ID: <strong><?php echo htmlspecialchars($upi_details['upi_id']); ?></strong></p>
                    </li>
                    <li>After successful payment, enter your transaction ID below</li>
                </ol>
            </div>

            <form action="" method="POST" class="payment-form">
                <div class="form-group">
                    <label for="payment_id">Transaction/Reference ID:</label>
                    <input type="text" id="payment_id" name="payment_id" required 
                           placeholder="Enter UPI transaction reference number">
                    <small>This is the reference number from your payment app</small>
                </div>
                <button type="submit" name="confirm_payment" class="submit-btn">Submit Payment Details</button>
            </form>
        <?php else: ?>
            <p class="error">Payment system is currently unavailable. Please contact the administrator.</p>
        <?php endif; ?>
        
        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>
