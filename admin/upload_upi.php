
<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

if (!isAdmin()) {
    header("Location: index.php");
    exit();
}

$message = "";

// Handle UPI QR Code Deletion
if (isset($_POST['delete_upi'])) {
    $current_upi = $conn->query("SELECT * FROM upi_details LIMIT 1")->fetch_assoc();
    
    if ($current_upi && file_exists($current_upi['qr_code_path'])) {
        // Delete the file from server
        unlink($current_upi['qr_code_path']);
    }
    
    // Delete from database
    $conn->query("DELETE FROM upi_details WHERE id = 1");
    $_SESSION['success_message'] = "UPI details deleted successfully.";
    header("Location: dashboard.php");
    exit();
}

if (isset($_POST['upload_upi'])) {
    $upi_id = $_POST['upi_id'];
    $target_dir = "../uploads/";

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Delete previous QR code if exists
    $current_upi = $conn->query("SELECT * FROM upi_details LIMIT 1")->fetch_assoc();
    if ($current_upi && file_exists($current_upi['qr_code_path'])) {
        unlink($current_upi['qr_code_path']);
    }

    $target_file = $target_dir . basename($_FILES["qr_code"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validate image
    $check = getimagesize($_FILES["qr_code"]["tmp_name"]);
    if ($check === false) {
        $message = "File is not an image.";
        $uploadOk = 0;
    }

    // File size check
    if ($_FILES["qr_code"]["size"] > 500000) {
        $message = "File is too large.";
        $uploadOk = 0;
    }

    // File format check
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        $message = "Only JPG, JPEG, PNG & GIF allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["qr_code"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO upi_details (id, upi_id, qr_code_path) VALUES (1, ?, ?) 
                                    ON DUPLICATE KEY UPDATE upi_id = VALUES(upi_id), qr_code_path = VALUES(qr_code_path)");
            $stmt->bind_param("ss", $upi_id, $target_file);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "UPI details uploaded successfully.";
                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Error saving to database.";
            }
        } else {
            $message = "Error uploading file.";
        }
    } else {
        $message .= " Upload failed.";
    }
}

// Fetch current UPI (optional display)
$current_upi = $conn->query("SELECT * FROM upi_details LIMIT 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload UPI QR Code</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h2>Upload UPI QR Code</h2>

        <?php if (!empty($message)): ?>
            <p class="error"><?php echo $message; ?></p>
        <?php endif; ?>

        <?php if (isset($current_upi['qr_code_path']) && file_exists($current_upi['qr_code_path'])): ?>
            <div style="margin-bottom: 20px; border: 1px solid #ddd; padding: 15px; background: #f9f9f9;">
                <h3>Current UPI Details</h3>
                <p><strong>UPI ID:</strong> <?php echo htmlspecialchars($current_upi['upi_id']); ?></p>
                <p>Current QR Code:</p>
                <img src="<?php echo htmlspecialchars($current_upi['qr_code_path']); ?>" alt="QR Code" style="max-width: 200px; margin-bottom: 15px;">
                
                <form action="" method="POST" onsubmit="return confirm('Are you sure you want to delete the current UPI details?');">
                    <button type="submit" name="delete_upi" style="background-color: #dc3545;">Delete Current UPI</button>
                </form>
            </div>
        <?php endif; ?>

        <h3><?php echo isset($current_upi) ? 'Update' : 'Upload'; ?> UPI Details</h3>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="upi_id">Your UPI ID:</label>
            <input type="text" id="upi_id" name="upi_id" value="<?php echo isset($current_upi['upi_id']) ? htmlspecialchars($current_upi['upi_id']) : ''; ?>" required>

            <label for="qr_code">Upload QR Code Image:</label>
            <input type="file" id="qr_code" name="qr_code" accept="image/*" required>

            <button type="submit" name="upload_upi"><?php echo isset($current_upi) ? 'Update' : 'Upload'; ?> UPI Details</button>
        </form>

        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>
