<?php
require 'login-validate.php'; // Include the login logic
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles/style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card login-card" id="loginCard">
                    <div class="card-header bg-white border-0 pt-5">
                        <h2 class="text-center mb-4">Account Login</h2>
                        <div class="d-grid gap-2">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="loginType" id="userLogin" value="user" autocomplete="off" checked>
                                <label class="btn btn-outline-primary w-50" for="userLogin">User Login</label>

                                <input type="radio" class="btn-check" name="loginType" id="adminLogin" value="admin" autocomplete="off">
                                <label class="btn btn-outline-danger w-50" for="adminLogin">Admin Login</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body px-5 pb-5">
                        <!-- Display error message -->
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= $_SESSION['error'];
                                unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="login.php">
                            <div class="mb-3">
                                <label for="identifier" class="form-label">Username or Email</label>
                                <input type="text" class="form-control" id="identifier" name="identifier" required placeholder="Enter your username or email">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Sign In</button>

                            <div class="text-center mt-4">
                                <a href="forgot_password.php" class="text-decoration-none">Forgot password?</a>
                                <p class="mt-2">Don't have an account? <a href="signup.php" class="text-decoration-none">Sign up</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add visual feedback for admin selection
        const adminLogin = document.getElementById('adminLogin');
        const loginCard = document.getElementById('loginCard');

        adminLogin.addEventListener('change', () => {
            loginCard.classList.toggle('admin-selected');
        });
    </script>
</body>
</html>