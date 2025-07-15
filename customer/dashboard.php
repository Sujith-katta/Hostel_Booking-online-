<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

if (!isCustomer()) {
    header("Location: index.php");
    exit();
}

// Fetch Room Types
$room_types = $conn->query("SELECT * FROM room_types");

// Handle Room Booking
if (isset($_POST['book_room'])) {
    $room_type_id = $_POST['room_type_id'];
    $mess_type = $_POST['mess_type'];
    $user_id = $_SESSION['user_id'];

    // Get room details to calculate total amount
    $stmt = $conn->prepare("SELECT base_price, laundry_fee, mess_veg_fee, mess_nonveg_fee FROM room_types WHERE id = ?");
    $stmt->bind_param("i", $room_type_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room_details = $result->fetch_assoc();

    if ($room_details) {
        $total_amount = $room_details['base_price'] + $room_details['laundry_fee'];
        if ($mess_type === 'veg') {
            $total_amount += $room_details['mess_veg_fee'];
        } else {
            $total_amount += $room_details['mess_nonveg_fee'];
        }

        $stmt = $conn->prepare("INSERT INTO bookings (user_id, room_type_id, mess_type, total_amount, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("isdd", $user_id, $room_type_id, $mess_type, $total_amount);
        if ($stmt->execute()) {
            echo "<p class='success'>Your booking request has been submitted and is awaiting admin approval. Total amount: ₹" . number_format($total_amount, 2) . "</p>";
        } else {
            echo "<p class='error'>Error submitting booking request.</p>";
        }
    } else {
        echo "<p class='error'>Invalid room type selected.</p>";
    }
}

// Fetch Customer's Bookings
$customer_bookings_query = "
    SELECT 
        b.id, 
        rt.name AS room_name, 
        b.mess_type, 
        b.total_amount, 
        b.booking_date, 
        b.status,
        b.payment_id
    FROM 
        bookings b
    JOIN 
        room_types rt ON b.room_type_id = rt.id
    WHERE 
        b.user_id = ?
    ORDER BY b.booking_date DESC
";
$stmt_bookings = $conn->prepare($customer_bookings_query);
$stmt_bookings->bind_param("i", $_SESSION['user_id']);
$stmt_bookings->execute();
$customer_bookings = $stmt_bookings->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>
        <p><a href="../includes/logout.php">Logout</a></p>

        <h3>Available Room Types</h3>
        <form action="" method="POST">
            <table>
                <thead>
                    <tr>
                        <th>Room Type</th>
                        <th>Description</th>
                        <th>Base Rent</th>
                        <th>Laundry Fee</th>
                        <th>Mess (Veg)</th>
                        <th>Mess (Non-Veg)</th>
                        <th>Select</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $room_types->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td>₹<?php echo number_format($row['base_price'], 2); ?></td>
                            <td>₹<?php echo number_format($row['laundry_fee'], 2); ?></td>
                            <td>₹<?php echo number_format($row['mess_veg_fee'], 2); ?></td>
                            <td>₹<?php echo number_format($row['mess_nonveg_fee'], 2); ?></td>
                            <td>
                                <input type="radio" name="room_type_id" value="<?php echo $row['id']; ?>" required>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($room_types->num_rows === 0): ?>
                        <tr><td colspan="7">No room types available. Please check back later.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <label for="mess_type">Select Mess Type:</label>
            <select id="mess_type" name="mess_type" required>
                <option value="veg">Vegetarian</option>
                <option value="non-veg">Non-Vegetarian</option>
            </select>
            <button type="submit" name="book_room">Book Room</button>
        </form>

        <h3>Your Bookings</h3>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Room Type</th>
                    <th>Mess Type</th>
                    <th>Total Amount</th>
                    <th>Booking Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $customer_bookings->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['room_name']); ?></td>
                        <td><?php echo ucfirst($row['mess_type']); ?></td>
                        <td>₹<?php echo number_format($row['total_amount'], 2); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($row['booking_date'])); ?></td>
                        <td>
                            <?php echo ucfirst($row['status']); ?>
                            <?php if ($row['status'] === 'pending' && $row['payment_id']): ?>
                                <br><small>(Payment submitted)</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'pending' && empty($row['payment_id'])): ?>
                                <a href="payment.php?booking_id=<?php echo $row['id']; ?>" class="btn-pay">Make Payment</a>
                            <?php elseif ($row['status'] === 'pending' && $row['payment_id']): ?>
                                <span class="payment-submitted">
                                    Transaction ID: <?php echo htmlspecialchars($row['payment_id']); ?>
                                </span>
                            <?php elseif ($row['status'] === 'accepted'): ?>
                                <a href="payment.php?booking_id=<?php echo $row['id']; ?>" class="btn-pay">View Payment</a>
                            <?php elseif ($row['status'] === 'paid'): ?>
                                <a href="bill.php?booking_id=<?php echo $row['id']; ?>" class="btn-bill">View Bill</a>
                            <?php else: ?>
                                <span><?php echo ucfirst($row['status']); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($customer_bookings->num_rows === 0): ?>
                    <tr><td colspan="7">You have no bookings yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
