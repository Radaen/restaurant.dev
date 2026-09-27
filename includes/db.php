<?php
// includes/db.php
$host = '127.0.0.1';
$db   = 'restaurant_db';
$user = 'root';
$pass = '';
$charset = 'utf8';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If DB doesn't exist, we might need to handle it or show setup page
    die("Database Connection Failed: " . $e->getMessage() . "<br><a href='setup_db.php'>Run Database Setup</a>");
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /Restaurant/auth/login.php");
        exit;
    }
}

function requireRole($roles) {
    requireLogin();
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], (array)$roles)) {
        die("Access Denied. You do not have permission to access this page.");
    }
}
?>
