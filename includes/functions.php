<?php
// Replace the current session_start() with this:
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function registerUser($conn, $username, $password, $role, $fullname, $email, $phone) {
    // Store plaintext password (INSECURE)
    $stmt = $conn->prepare("INSERT INTO users (username, password, role, fullname, email, phone) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $password, $role, $fullname, $email, $phone);
    return $stmt->execute();
}

function loginUser($conn, $username, $password, $role) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if ($password === $user['password']) { // plain text check
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
    }
    return false;
}




function isAdmin() {
    return isset($_SESSION['role']) && 
           $_SESSION['role'] === 'admin' && 
           isset($_SESSION['admin_authenticated']) && 
           $_SESSION['admin_authenticated'] === true;
}

function isCustomer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'customer';
}

function logout() {
    session_unset();
    session_destroy();
    header("Location: /hostel_booking/index.php"); // Redirect to a landing page
    exit();
}
?>