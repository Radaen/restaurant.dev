<?php
// setup_db.php
$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents(__DIR__ . '/database.sql');
    
    $pdo->exec($sql);
    echo "Database created and populated successfully! <a href='index.php'>Go to Dashboard</a>";
} catch (PDOException $e) {
    die("DB Setup Failed: " . $e->getMessage());
}
?>
