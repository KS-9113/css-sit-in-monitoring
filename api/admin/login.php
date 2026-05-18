<?php
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/login.php');
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = getDB()->prepare('SELECT * FROM admins WHERE username = ?');
$stmt->execute([$username]);
$admin = $stmt->fetch();

if (!$admin || !password_verify($password, $admin['password'])) {
    redirect('/admin/login.php?toast=' . urlencode('Invalid admin credentials.') . '&toast_type=error');
}

$_SESSION['admin_id'] = $admin['id'];
$_SESSION['admin_name'] = $admin['full_name'];

redirect('/admin/dashboard.php?toast=' . urlencode('Welcome, Administrator.'));
