<?php
/**
 * Database configuration for XAMPP
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'ccs_sit_in_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', '/ccs-sit-in-monitoring');

function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        // 1. Set PHP's internal script timezone execution context
        date_default_timezone_set('Asia/Manila');

        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        // 2. Force the MySQL session connection to match the same timezone (+08:00)
        $pdo->exec("SET time_zone = '+08:00';");
    }
    return $pdo;
}
