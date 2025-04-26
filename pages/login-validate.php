<?php
session_start();
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['identifier']; // This will hold either the username or email
    $password = $_POST['password'];
    $loginType = $_POST['loginType']; // Get the selected login type (user or admin)

    // Check if the identifier matches either username or email in the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($loginType === 'admin' && $user['role'] !== 'admin') {
            // If admin login is selected but the user is not an admin
            $_SESSION['error'] = 'Access denied. Admins only.';
        } else {
            // Successful login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header("Location: ../index.php"); // Redirect to the dashboard
            exit();
        }
    } else {
        // Invalid credentials
        $_SESSION['error'] = 'Invalid username/email or password.';
    }
}
?>