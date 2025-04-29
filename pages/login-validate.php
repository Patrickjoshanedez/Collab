<?php
// Start a new session or resume the existing session
session_start();

// Include the database connection file
require '../includes/db.php';

// Check if the request method is POST and required fields are set
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' // Ensure the form was submitted via POST
    && isset($_POST['identifier'], $_POST['password'], $_POST['role']) // Check if identifier, password, and role are provided
) {
    // Trim whitespace from the identifier (username or email)
    $identifier = trim($_POST['identifier']);
    // Get the password from the POST request
    $password = $_POST['password'];
    // Determine the role based on the submitted value (default to 'User' if not 'admin')
    $role = ($_POST['role'] === 'admin') ? 'Admin' : 'User';

    // Prepare an SQL statement to find a user with the given identifier and role
    $stmt = $pdo->prepare("SELECT User_ID, username, password, Role FROM users WHERE (username = ? OR email = ?) AND Role = ?");
    // Execute the query with the provided identifier and role
    $stmt->execute([$identifier, $identifier, $role]);
    // Fetch the user data as an associative array
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the user exists and the provided password matches the hashed password in the database
    if ($user && password_verify($password, $user['password'])) {
        // Regenerate the session ID to prevent session fixation attacks
        session_regenerate_id(true);
        // Store the user's ID in the session
        $_SESSION['user_id'] = $user['User_ID'];
        // Store the user's username in the session
        $_SESSION['username'] = $user['username'];
        // Store the user's role in the session
        $_SESSION['role'] = $user['Role'];

        // Redirect the user based on their role
        if ($user['Role'] === 'Admin') {
            // Redirect to the admin dashboard if the user is an admin
            header('Location: ../dashboard/admin/admin-dashboard.php');
        } else {
            // Redirect to the user dashboard if the user is not an admin
            header('Location: ../dashboard/user/user-dashboard.php');
        }
        // Exit the script to ensure no further code is executed
        exit;
    }
}

// If the authentication fails, set an error message in the session
$_SESSION['error'] = 'Invalid username or password.';
// Redirect back to the login page
header('Location: login.php');
// Exit the script to ensure no further code is executed
exit;
?>
