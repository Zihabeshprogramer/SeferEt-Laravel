<?php

$host = '127.0.0.1';
$dbname = 'seferet_db';
$username = 'root';
$password = 'Seyako@0011';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Users Table Structure ===\n";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "Column: {$column['Field']} - Type: {$column['Type']} - Null: {$column['Null']} - Default: " . ($column['Default'] ?? 'null') . "\n";
    }
    
    echo "\n=== Sample User Data ===\n";
    $stmt = $pdo->query("SELECT * FROM users WHERE id IN (3, 9) LIMIT 2");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "\nUser ID {$user['id']}:\n";
        foreach ($user as $key => $value) {
            echo "  $key: " . ($value ?? 'null') . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

?>