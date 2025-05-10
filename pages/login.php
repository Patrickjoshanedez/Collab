<?php
session_start();

// Check if the user is already logged in via session or cookies
if (isset($_SESSION['user_id']) || (isset($_COOKIE['user_id']) && isset($_COOKIE['role']))) {
    // Restore session from cookies if necessary
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = $_COOKIE['user_id'];
        $_SESSION['username'] = $_COOKIE['username'];
        $_SESSION['role'] = $_COOKIE['role'];
    }

    // Redirect based on role
    if ($_SESSION['role'] === 'admin') {
        header('Location: ../dashboard/admin/admin-dashboard.php');
    } else {
        header('Location: ../dashboard/user/user-dashboard.php');
    }
    exit;
}

// Retrieve and clear any error message set by login-validate.php
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles/style.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <div class="container mt-5" style="max-width: 400px;">
        <div class="card" id="loginCard">
            <div class="card-header text-center">
                <h2>Account Login</h2>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="login-validate.php">
                    <div class="mb-3">
                        <label for="identifier" class="form-label">Username or Email</label>
                        <input type="text" class="form-control" id="identifier" name="identifier" required placeholder="Enter your username or email" autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                    </div>

                    <div class="mb-3">
                        <label class="form-label me-3">Login as:</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="role" id="userLogin" value="user" checked>
                            <label class="form-check-label" for="userLogin">User</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="role" id="adminLogin" value="admin">
                            <label class="form-check-label" for="adminLogin">Admin</label>
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>

                    <div class="mb-3">
                        <div class="g-recaptcha" data-sitekey="6LfSCjUrAAAAAN6-MpRpTbijCWGdNURTZUkgGzTn"></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Sign In</button>

                    <div class="text-center mt-3">
                        <a href="forgot_password.php">Forgot password?</a><br>
                        <a href="signup.php">Don't have an account? Sign up</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
