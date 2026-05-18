<?php
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/register.php');
}

$idNumber = trim($_POST['id_number'] ?? '');
$email = trim($_POST['email'] ?? '');
$firstName = trim($_POST['first_name'] ?? '');
$middleName = trim($_POST['middle_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$course = $_POST['course'] ?? '';
$yearLevel = $_POST['year_level'] ?? '';
$address = trim($_POST['address'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($password !== $confirm) {
    redirect('/register.php?toast=' . urlencode('Passwords do not match.') . '&toast_type=error');
}

if (strlen($password) < 6) {
    redirect('/register.php?toast=' . urlencode('Password must be at least 6 characters.') . '&toast_type=error');
}

$db = getDB();
$check = $db->prepare('SELECT id FROM students WHERE id_number = ? OR email = ?');
$check->execute([$idNumber, $email]);
if ($check->fetch()) {
    redirect('/register.php?toast=' . urlencode('ID Number or Email already registered.') . '&toast_type=error');
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $db->prepare('INSERT INTO students (id_number, email, first_name, middle_name, last_name, course, year_level, address, password, remaining_sessions) VALUES (?,?,?,?,?,?,?,?,?,30)');
$stmt->execute([$idNumber, $email, $firstName, $middleName ?: null, $lastName, $course, $yearLevel, $address, $hash]);

redirect('/login.php?toast=' . urlencode('Account Registered Successfully'));
