<?php
require_once 'includes/db.php';
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$pdo->exec("UPDATE users SET password_hash = '$hash' WHERE username = 'admin'");
echo "Password reset to admin123";
?>
