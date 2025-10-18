<?php

// Simple database check
$host = '127.0.0.1';
$dbname = 'seferet_db';
$username = 'root';
$password = 'Seyako@0011';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Count service requests
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM service_requests");
    $count = $stmt->fetchColumn();
    echo "Total Service Requests: $count\n";
    
    // Get latest service requests
    $stmt = $pdo->query("SELECT id, status, package_id, provider_type, provider_id, requested_quantity, created_at FROM service_requests ORDER BY created_at DESC LIMIT 5");
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nLatest Service Requests:\n";
    foreach ($requests as $request) {
        echo "ID: {$request['id']}, Status: {$request['status']}, Package: {$request['package_id']}, Provider: {$request['provider_type']}:{$request['provider_id']}, Quantity: {$request['requested_quantity']}, Created: {$request['created_at']}\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}