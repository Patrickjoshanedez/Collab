<?php
// File: reset-password.php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['reset_code_verified'], $_SESSION['reset_email']) || !$_SESSION['reset_code_verified']) {
    header("Location: forgot-password.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword === $confirmPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password = ?, reset_code = NULL WHERE email = ?")
            ->execute([$hashedPassword, $_SESSION['reset_email']]);

        unset($_SESSION['reset_code_verified'], $_SESSION['reset_email'], $_SESSION['email']);
        $_SESSION['success'] = 'Password reset successful. Please login.';
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = 'Passwords do not match.';
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/style.css">
    <title>Reset Password</title>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Reset Password</h1>
            <p>Please enter your new password below.</p>

            <?php if (isset($_SESSION['error'])): ?>
                <p style="color: #F74141; border: 1px solid #F74141; padding: 0.5rem; border-radius: 0.25rem;">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </p>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <p style="color: #77DD77; border: 1px solid #77DD77; padding: 0.5rem; border-radius: 0.25rem;">
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </p>
            <?php endif; ?>

            <form action="reset-password.php" method="POST">
                <div class="form-group">
                    <input type="password" name="password" placeholder="New Password" required>
                </div>
                <div class="form-group">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                <button type="submit">Reset Password</button>
            </form>
        </div>
    </div>
</body>
</html>
