<?php
require_once 'includes/db.php';

try {
    $admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("UPDATE users SET password_hash = '$admin_hash' WHERE username = 'admin'");
    
    $daen_hash = password_hash('admin', PASSWORD_DEFAULT);
    $pdo->exec("UPDATE users SET password_hash = '$daen_hash' WHERE username = 'Daen'");
    
    echo "Passwords updated successfully! You can now login:\n";
    echo "- Username: admin / Password: admin123\n";
    echo "- Username: Daen / Password: admin\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
