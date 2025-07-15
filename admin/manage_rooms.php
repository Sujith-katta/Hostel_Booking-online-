
<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

if (!isAdmin()) {
    header("Location: index.php");
    exit();
}

// Update Booking Status
if (isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['status'];
    $verify_payment = isset($_POST['verify_payment']) ? 1 : 0;

    // If marking as accepted, ensure payment is verified if exists
    if ($new_status === 'accepted') {
        $stmt = $conn->prepare("UPDATE bookings SET status = ?, is_payment_verified = ? WHERE id = ?");
        $stmt->bind_param("sii", $new_status, $verify_payment, $booking_id);
    } else {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $booking_id);
    }

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Booking status updated successfully!";
        header("Location: manage_bookings.php");
        exit();
    } else {
        echo "<p class='error'>Error updating booking status.</p>";
    }
}

// Fetch Bookings with User and Room Details
$bookings_query = "
    SELECT 
        b.id, 
        u.fullname, 
        u.email,
        u.phone,
        rt.name AS room_name, 
        b.mess_type, 
        b.total_amount, 
        b.booking_date, 
        b.status,
        b.payment_id,
        b.is_payment_verified
    FROM 
        bookings b
    JOIN 
        users u ON b.user_id = u.id
    JOIN 
        room_types rt ON b.room_type_id = rt.id
    ORDER BY 
        CASE WHEN b.status = 'pending' THEN 0 ELSE 1 END,
        b.booking_date DESC
";
$bookings = $conn->query($bookings_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .payment-info {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 5px 0;
        }
        .verified {
            color: green;
            font-weight: bold;
        }
        .unverified {
            color: orange;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Bookings</h2>
        <?php if (isset($_SESSION['success_message'])): ?>
            <p class="success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Room</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Payment Info</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $bookings->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td>
                            <?php echo htmlspecialchars($row['fullname']); ?><br>
                            <?php echo htmlspecialchars($row['email']); ?><br>
                            <?php echo htmlspecialchars($row['phone']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['room_name']); ?><br>
                            Mess: <?php echo ucfirst($row['mess_type']); ?>
                        </td>
                        <td>₹<?php echo number_format($row['total_amount'], 2); ?></td>
                        <td><?php echo date('d M Y H:i', strtotime($row['booking_date'])); ?></td>
                        <td><?php echo ucfirst($row['status']); ?></td>
                        <td>
                            <?php if ($row['payment_id']): ?>
                                <div class="payment-info">
                                    <strong>Transaction ID:</strong><br>
                                    <?php echo htmlspecialchars($row['payment_id']); ?><br>
                                    Status: 
                                    <?php if ($row['is_payment_verified']): ?>
                                        <span class="verified">Verified</span>
                                    <?php else: ?>
                                        <span class="unverified">Unverified</span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                No payment submitted
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'pending'): ?>
                                <form action="" method="POST" class="action-form">
                                    <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="status" value="accepted">
                                    <?php if ($row['payment_id']): ?>
                                        <label>
                                            <input type="checkbox" name="verify_payment" checked>
                                            Verify Payment
                                        </label><br>
                                    <?php endif; ?>
                                    <button type="submit" name="update_status" class="accept-btn">Accept</button>
                                </form>
                                <form action="" method="POST" class="action-form">
                                    <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" name="update_status" class="reject-btn">Reject</button>
                                </form>
                            <?php else: ?>
                                <span><?php echo ucfirst($row['status']); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($bookings->num_rows === 0): ?>
                    <tr><td colspan="8">No bookings found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>
