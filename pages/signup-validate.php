<?php
// Start session to manage user login state
session_start();

// Include database connection (PDO instance $pdo)
require_once '../includes/db.php';

// Handle form submission
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize inputs
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phoneNo = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    $terms = isset($_POST['terms']);

    // Validate required fields
    if (empty($username) || empty($name) || empty($email) || empty($phoneNo) || empty($password) || empty($confirmPassword)) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    } elseif (!preg_match('/^\d{11}$/', $phoneNo)) {
        $errors[] = "Invalid phone number. It must be 11 digits.";
    } elseif ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    } elseif (!$terms) {
        $errors[] = "You must agree to the Terms & Conditions.";
    } else {
        try {
            // Check if the username or email already exists
            $stmt = $pdo->prepare("SELECT User_ID FROM Users WHERE Username = :username OR Email = :email");
            $stmt->execute(['username' => $username, 'email' => $email]);

            if ($stmt->rowCount() > 0) {
                $errors[] = "An account with this username or email already exists.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert the new user into the database
                $sql = "INSERT INTO Users (Username, Name, Contact_no, Address, Purchase_History, Role, Email, Password, reset_token)
                        VALUES (:username, :name, :contact_no, :address, :purchase_history, :role, :email, :password, :reset_token)";
                $stmt = $pdo->prepare($sql);

                // Prepare parameters for the query
                $params = [
                    'username'         => $username,
                    'name'             => $name,
                    'contact_no'       => $phoneNo,
                    'address'          => '', // Default empty value for Address
                    'purchase_history' => '', // Default empty value for Purchase_History
                    'role'             => 'User', // Default role is 'User'
                    'email'            => $email,
                    'password'         => $hashedPassword,
                    'reset_token'      => null // Default null value for reset_token
                ];

                if ($stmt->execute($params)) {
                    $success = "Account created successfully!";
                } else {
                    $errors[] = "Failed to create account. Please try again.";
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>