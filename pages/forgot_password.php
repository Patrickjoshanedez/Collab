<?php
// File: forgot_password.php
session_start();
require '../includes/db.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate a reset code
        $reset_code = rand(100000, 999999); // Use $reset_code consistently
        
        // Update the reset code in the database
        $pdo->prepare("UPDATE users SET reset_code = ? WHERE email = ?")->execute([$reset_code, $email]);

        // Store the email in session
        $_SESSION['email'] = $email;

        // Setup PHPMailer to send the reset code
        $mail = new PHPMailer(true);
        try {
            // Configure SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'distructatlas@gmail.com';  // Your Gmail address
            $mail->Password = 'jeos byzb xeag mued';      // Your Gmail app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Sender and recipient details
            $mail->setFrom('distructatlas@gmail.com', 'Your App Name');
            $mail->addAddress($email);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Code';
            $mail->Body = "<p>Your reset code is: <strong>{$reset_code}</strong></p>";
            $mail->AltBody = "Your reset code is: {$reset_code}";  // For non-HTML email clients

            // Send email
            $mail->send();

            // Success: Redirect to send-code.php
            $_SESSION['success'] = 'Code sent. Check your email.';
            header("Location: send-code.php");
            exit();
        } catch (Exception $e) {
            // Error: Show error message
            $_SESSION['error'] = "Mailer Error: {$mail->ErrorInfo}";
            header("Location: forgot_password.php");
            exit();
        }
    } else {
        // If email does not exist in the database
        $_SESSION['error'] = 'No user found with that email.';
        header("Location: forgot_password.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" href="../assets/padayunITLogo.png" />
  <title>Forgot Password</title>
</head>

<body class="bg-light">
  <div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
      <div class="text-center">
        <img src="images/images.png" alt="This is a logo" class="img-fluid mb-3" style="max-width: 150px;">
        <h1 class="h4 mb-3">Forgot Password</h1>
        <p class="text-muted">Please enter your email address</p>
      </div>

      <!-- Display error message if set -->
      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" role="alert">
          <?= $_SESSION['error'];
          unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>

      <!-- Display success message if set -->
      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success" role="alert">
          <?= $_SESSION['success'];
          unset($_SESSION['success']); ?>
        </div>
      <?php endif; ?>

      <!-- Form to enter email address -->
      <form action="forgot_password.php" method="POST">
        <div class="mb-3">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Submit</button>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
