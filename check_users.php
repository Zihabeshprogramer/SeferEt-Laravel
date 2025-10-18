<?php

// Check existing users
$host = '127.0.0.1';
$dbname = 'seferet_db';
$username = 'root';
$password = 'Seyako@0011';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Users in Database ===\n";
    $stmt = $pdo->query("SELECT id, name, email, created_at FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Created: {$user['created_at']}\n";
    }
    
    echo "\n=== Service Requests by User ===\n";
    $stmt = $pdo->query("
        SELECT 
            sr.agent_id, 
            sr.provider_id, 
            COUNT(*) as request_count,
            u1.name as agent_name,
            u2.name as provider_name
        FROM service_requests sr
        LEFT JOIN users u1 ON sr.agent_id = u1.id
        LEFT JOIN users u2 ON sr.provider_id = u2.id
        GROUP BY sr.agent_id, sr.provider_id
    ");
    $requestsByUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($requestsByUser as $row) {
        echo "Agent: {$row['agent_name']} (ID: {$row['agent_id']}) -> Provider: {$row['provider_name']} (ID: {$row['provider_id']}) - {$row['request_count']} requests\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

?>