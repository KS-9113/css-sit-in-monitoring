<?php
/**
 * Run once after importing schema.sql to set admin password (admin123)
 * Visit: http://localhost/ccs-sit-in-monitoring/install.php
 * Delete this file after installation for security.
 */
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDB();
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE admins SET password = ? WHERE username = ?');
    $stmt->execute([$hash, 'admin']);
    echo '<h2>Installation complete</h2>';
    echo '<p>Admin password set to <strong>admin123</strong> (hashed).</p>';
    echo '<p><a href="' . BASE_URL . '/index.php">Go to Home</a></p>';
    echo '<p style="color:red;">Delete install.php for security.</p>';
} catch (Exception $e) {
    echo '<h2>Error</h2><p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p>Import database/schema.sql in phpMyAdmin first.</p>';
}
