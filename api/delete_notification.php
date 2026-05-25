<?php
require_once __DIR__ . '/../includes/functions.php';
requireStudent();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        archiveNotification($id, 'student', (int) $_SESSION['student_id']);
    }
}
redirect('/student/notifications.php');