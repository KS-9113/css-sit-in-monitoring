<?php
/**
 * Database configuration for XAMPP
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'ccs_sit_in_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', '/ccs-sit-in-monitoring');

<?php
function getDB() {
    $host = isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'localhost';
    $db   = isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'your_local_db_name';
    $user = isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'root';
    $pass = isset($_ENV['DB_PASSWORD']) ? $_ENV['DB_PASSWORD'] : ''; // Local password
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
         return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
         throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}