<?php
$host = 'localhost';
$db   = 'surplus_shop';
$user = 'root'; // Default for XAMPP
$pass = '';     // Default password is blank in XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
