<?php
session_start();
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredCode = (int)$_POST['code'];
    $email = $_SESSION['email'] ?? null;

    if (!$email) {
        $_SESSION['error'] = 'Session expired. Try again.';
        header("Location: forgot_password.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT reset_code FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && (int)$user['reset_code'] === $enteredCode) {
        $_SESSION['reset_code_verified'] = true;
        $_SESSION['reset_email'] = $email;
        header("Location: reset-password.php");
        exit();
    } else {
        $_SESSION['error'] = 'Invalid reset code.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Enter Reset Code</title>
</head>

<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
            <div class="text-center mb-4">
                <h1 class="h4">Enter Reset Code</h1>
                <p class="text-muted">Please enter the 6-digit code sent to your email.</p>
            </div>

            <!-- Display error message -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Display success message -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success" role="alert">
                    <?= $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <!-- Form to enter reset code -->
            <form action="send-code.php" method="POST">
                <div class="mb-3">
                    <label for="code" class="form-label">Reset Code</label>
                    <input type="number" class="form-control" id="code" name="code" placeholder="Enter code" maxlength="6" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Submit</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>