<?php
require_once __DIR__ . '/../includes/functions.php';
requireStudent();

$id = (int) ($_POST['id'] ?? 0);
$stmt = getDB()->prepare("UPDATE sit_in_records SET status = 'User Cancelled' WHERE id = ? AND student_id = ? AND status IN ('Reserved','Approved')");
$stmt->execute([$id, $_SESSION['student_id']]);

redirect('/student/history.php?toast=' . urlencode('Reservation cancelled.'));
