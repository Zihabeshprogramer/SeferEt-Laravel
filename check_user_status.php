<?php

// Check and update user status
$host = '127.0.0.1';
$dbname = 'seferet_db';
$username = 'root';
$password = 'Seyako@0011';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Checking User Status ===\n";
    
    // Check current status of users 9 and 3
    $stmt = $pdo->prepare("SELECT id, name, email, status, is_active FROM users WHERE id IN (3, 9)");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "User {$user['id']}: {$user['name']} ({$user['email']}) - Status: " . ($user['status'] ?? 'null') . ", Active: " . ($user['is_active'] ?? 'null') . "\n";
    }
    
    echo "\n=== Activating Users ===\n";
    
    // Activate both users if they have status/is_active columns
    $updateStmt = $pdo->prepare("UPDATE users SET status = 'active', is_active = 1 WHERE id IN (3, 9)");
    $result = $updateStmt->execute();
    
    if ($result) {
        echo "Updated user statuses successfully\n";
    } else {
        echo "Failed to update user statuses\n";
    }
    
    echo "\n=== Updated User Status ===\n";
    $stmt->execute();
    $updatedUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($updatedUsers as $user) {
        echo "User {$user['id']}: {$user['name']} ({$user['email']}) - Status: " . ($user['status'] ?? 'null') . ", Active: " . ($user['is_active'] ?? 'null') . "\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

?>