<?php
/**
 * Secure Database Connection Helper
 * 
 * Usage: Include this file instead of hardcoding credentials
 * require_once __DIR__ . '/db_helper.php';
 * $pdo = getSecureDbConnection();
 */

function getSecureDbConnection(): PDO
{
    // Load .env file if not already loaded
    if (!function_exists('env')) {
        $envFile = dirname(__DIR__) . '/.env';
        
        if (!file_exists($envFile)) {
            throw new Exception('.env file not found');
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }
    
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $dbname = getenv('DB_DATABASE') ?: 'seferet_db';
    $username = getenv('DB_USERNAME') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
}

function env(string $key, $default = null)
{
    $value = getenv($key);
    return $value !== false ? $value : $default;
}
