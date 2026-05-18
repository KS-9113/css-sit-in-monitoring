<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$id = (int) ($_POST['id'] ?? 0);
$params = [
    trim($_POST['id_number'] ?? ''),
    trim($_POST['email'] ?? ''),
    trim($_POST['first_name'] ?? ''),
    trim($_POST['middle_name'] ?? '') ?: null,
    trim($_POST['last_name'] ?? ''),
    $_POST['course'] ?? '',
    $_POST['year_level'] ?? '',
    trim($_POST['address'] ?? ''),
    (int) ($_POST['remaining_sessions'] ?? 30),
];

$sql = 'UPDATE students SET id_number=?, email=?, first_name=?, middle_name=?, last_name=?, course=?, year_level=?, address=?, remaining_sessions=?';
$password = $_POST['password'] ?? '';
if ($password !== '') {
    $sql .= ', password=?';
    $params[] = password_hash($password, PASSWORD_DEFAULT);
}
$sql .= ' WHERE id=?';
$params[] = $id;

getDB()->prepare($sql)->execute($params);
redirect('/admin/students.php?toast=' . urlencode('Student updated.'));
