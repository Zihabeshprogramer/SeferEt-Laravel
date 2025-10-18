<?php

$host = '127.0.0.1';
$dbname = 'seferet_db';
$username = 'root';
$password = 'Seyako@0011';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Updating Email Verification ===\n";
    
    // Update user 3 to have email verification
    $stmt = $pdo->prepare("UPDATE users SET email_verified_at = NOW() WHERE id = 3");
    $result = $stmt->execute();
    
    if ($result) {
        echo "Updated email verification for user 3\n";
    } else {
        echo "Failed to update email verification\n";
    }
    
    // Check updated status
    echo "\n=== Updated User 3 ===\n";
    $stmt = $pdo->prepare("SELECT id, name, email, status, email_verified_at FROM users WHERE id = 3");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Status: {$user['status']}, Verified: {$user['email_verified_at']}\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

?>