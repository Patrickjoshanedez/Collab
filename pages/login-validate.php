
<?php
// login-validate.php: handles POST, authentication, and redirects
session_start();
require '../includes/db.php';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['identifier'], $_POST['password'], $_POST['role'])
) {
    $identifier = trim($_POST['identifier']);
    $password   = $_POST['password'];
    $role       = ($_POST['role'] === 'admin') ? 'Admin' : 'User';

    // Prepare and execute lookup
    $stmt = $pdo->prepare("SELECT User_ID, username, password, Role FROM users WHERE (username = ? OR email = ?) AND Role = ?");
    $stmt->execute([$identifier, $identifier, $role]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Secure session cookie settings
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['User_ID'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['Role'];

        // Redirect based on role
        if ($user['Role'] === 'Admin') {
            header('Location: ../dashboard/admin.php');
        } else {
            header('Location: ../dashboard/index.html');
        }
        exit;
    }
}

// If we reach here, authentication failed
$_SESSION['error'] = 'Invalid username or password.';
header('Location: login.php');
exit;
?>
