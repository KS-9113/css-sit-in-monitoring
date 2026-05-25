<?php
// 1. Core Global Configuration Settings
define('BASE_URL', '/'); 

// Manually force TiDB parameters to bypass Vercel env bugs
    $host = 'gateway01.us-east-1.prod.aws.tidbcloud.com'; // Replace with your real TiDB host string
    $db   = 'test';                                       // Replace with your real TiDB database name
    $user = 'D6Rek4SzruzhD1x.root';
    $pass = 'PASTE_YOUR_ACTUAL_TIDB_PASSWORD_HERE';       // Replace with your cleartext TiDB password
    $port = '4000';
    $charset = 'utf8mb4';

// 2. Database Connection Function
function getDB() {
    // Look everywhere Vercel might store your environment keys
    $host = $_SERVER['DB_HOST'] ?? $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
    $db   = $_SERVER['DB_NAME'] ?? $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'ccs_sit_in_db';
    $user = $_SERVER['DB_USER'] ?? $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
    $pass = $_SERVER['DB_PASSWORD'] ?? $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';
    $port = $_SERVER['DB_PORT'] ?? $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306'; 
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Detect if we are on Vercel by checking if a cloud host is captured
    if ($host !== 'localhost') {
        $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/pki/tls/certs/ca-bundle.crt';
    }

    try {
         return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
         throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}