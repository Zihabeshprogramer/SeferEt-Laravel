<?php

$host = '127.0.0.1';
$dbname = 'seferet_db';
$username = 'root';
$password = 'Seyako@0011';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Hash the password 'password'
    $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
    
    echo "=== Resetting Provider Password ===\n";
    
    // Update user 3 password to 'password'
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = 3");
    $result = $stmt->execute([$hashedPassword]);
    
    if ($result) {
        echo "Password updated successfully for user 3 (b2b@test.com)\n";
        echo "New password: password\n";
    } else {
        echo "Failed to update password\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

?>