
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

    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $booking_id);
    if ($stmt->execute()) {
        echo "<p class='success'>Booking status updated successfully!</p>";
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
        b.payment_id
    FROM 
        bookings b
    JOIN 
        users u ON b.user_id = u.id
    JOIN 
        room_types rt ON b.room_type_id = rt.id
    ORDER BY b.booking_date DESC
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
</head>
<body>
    <div class="container">
        <h2>Manage Bookings</h2>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Room Type</th>
                    <th>Mess Type</th>
                    <th>Total Amount</th>
                    <th>Booking Date</th>
                    <th>Status</th>
                    <th>Transaction ID</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $bookings->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['room_name']); ?></td>
                        <td><?php echo ucfirst($row['mess_type']); ?></td>
                        <td>₹<?php echo number_format($row['total_amount'], 2); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($row['booking_date'])); ?></td>
                        <td><?php echo ucfirst($row['status']); ?></td>
                        <td><?php echo $row['payment_id'] ? htmlspecialchars($row['payment_id']) : 'Not Paid '; ?></td>
                        <td>
                            <?php if ($row['status'] === 'pending'): ?>
                                <form action="" method="POST" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="status" value="accepted">
                                    <button type="submit" name="update_status">Accept</button>
                                </form>
                                <form action="" method="POST" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" name="update_status">Reject</button>
                                </form>
                            <?php else: ?>
                                <button disabled><?php echo ucfirst($row['status']); ?></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($bookings->num_rows === 0): ?>
                    <tr><td colspan="11">No pending bookings.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>
