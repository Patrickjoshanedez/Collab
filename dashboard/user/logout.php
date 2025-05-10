<?php
session_start();

// Clear session variables
session_unset();
session_destroy();

// Clear cookies
setcookie('user_id', '', time() - 3600, '/');
setcookie('username', '', time() - 3600, '/');
setcookie('role', '', time() - 3600, '/');

// Redirect to the login page
header('Location: ../../pages/login.php');
exit;
?>