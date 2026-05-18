<?php
require_once __DIR__ . '/../includes/functions.php';
requireStudent();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student/profile.php');
}

$studentId = (int) $_SESSION['student_id'];
$student = getStudentById($studentId);
$firstName = trim($_POST['first_name'] ?? '');
$middleName = trim($_POST['middle_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

$db = getDB();
$check = $db->prepare('SELECT id FROM students WHERE email = ? AND id != ?');
$check->execute([$email, $studentId]);
if ($check->fetch()) {
    redirect('/student/profile.php?toast=' . urlencode('Email already in use.') . '&toast_type=error');
}

$profilePic = $student['profile_picture'];
if (!empty($_FILES['profile_picture']['name'])) {
    $uploaded = uploadProfilePicture($_FILES['profile_picture']);
    if ($uploaded) {
        $profilePic = $uploaded;
    }
}

$sql = 'UPDATE students SET first_name=?, middle_name=?, last_name=?, email=?, address=?, profile_picture=?';
$params = [$firstName, $middleName ?: null, $lastName, $email, $address, $profilePic];

if ($password !== '') {
    if ($password !== $confirm) {
        redirect('/student/profile.php?toast=' . urlencode('Passwords do not match.') . '&toast_type=error');
    }
    $sql .= ', password=?';
    $params[] = password_hash($password, PASSWORD_DEFAULT);
}
$sql .= ' WHERE id=?';
$params[] = $studentId;

$db->prepare($sql)->execute($params);
$_SESSION['student_name'] = getStudentFullName(getStudentById($studentId));

redirect('/student/profile.php?toast=' . urlencode('Profile updated successfully.'));
