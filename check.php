<?php
require_once 'includes/db.php';
try {
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll();
    echo "Users in DB:\n";
    print_r($users);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
