<?php
require_once __DIR__ . '/../includes/functions.php';
requireStudent();

$recordId = (int) ($_POST['record_id'] ?? 0);
$rating = (int) ($_POST['rating'] ?? 0);
$comments = trim($_POST['comments'] ?? '');

if ($rating < 1 || $rating > 5 || $comments === '') {
    redirect('/student/history.php?toast=' . urlencode('Invalid feedback.') . '&toast_type=error');
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM sit_in_records WHERE id = ? AND student_id = ? AND status = 'Completed'");
$stmt->execute([$recordId, $_SESSION['student_id']]);
$record = $stmt->fetch();

if (!$record) {
    redirect('/student/history.php?toast=' . urlencode('Record not found.') . '&toast_type=error');
}

$exists = $db->prepare('SELECT id FROM feedback WHERE sit_in_record_id = ?');
$exists->execute([$recordId]);
if ($exists->fetch()) {
    redirect('/student/history.php?toast=' . urlencode('Feedback already submitted.') . '&toast_type=error');
}

$db->prepare('INSERT INTO feedback (sit_in_record_id, student_id, laboratory_id, rating, comments) VALUES (?,?,?,?,?)')
    ->execute([$recordId, $_SESSION['student_id'], $record['laboratory_id'], $rating, $comments]);

redirect('/student/history.php?toast=' . urlencode('Thank you for your feedback!'));
