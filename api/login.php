<?php
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/login.php');
}

$idNumber = trim($_POST['id_number'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = getDB()->prepare('SELECT * FROM students WHERE id_number = ?');
$stmt->execute([$idNumber]);
$student = $stmt->fetch();

if (!$student || !password_verify($password, $student['password'])) {
    redirect('/login.php?toast=' . urlencode('Invalid ID Number or password.') . '&toast_type=error');
}

$_SESSION['student_id'] = $student['id'];
$_SESSION['student_name'] = getStudentFullName($student);

redirect('/student/dashboard.php?toast=' . urlencode('Log in Successfully.'));
