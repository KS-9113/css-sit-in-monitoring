<?php
function getDB() {
    $host = isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'localhost';
    $db   = isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'your_local_db_name';
    $user = isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'root';
    $pass = isset($_ENV['DB_PASSWORD']) ? $_ENV['DB_PASSWORD'] : '';
    $port = isset($_ENV['DB_PORT']) ? $_ENV['DB_PORT'] : '3306'; 
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    if (isset($_ENV['DB_HOST'])) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/pki/tls/certs/ca-bundle.crt';
    }

    try {
         return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
         throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}