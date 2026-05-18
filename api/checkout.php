<?php
require_once __DIR__ . '/../includes/functions.php';
requireStudent();

$id = (int) ($_POST['id'] ?? 0);
$db = getDB();
$stmt = $db->prepare("SELECT * FROM sit_in_records WHERE id = ? AND student_id = ? AND status = 'On Going'");
$stmt->execute([$id, $_SESSION['student_id']]);
$record = $stmt->fetch();

if ($record) {
    $now = date('Y-m-d H:i:s');
    $dur = computeDuration($record['time_in'], $now);
    $db->prepare("UPDATE sit_in_records SET time_out = ?, duration_minutes = ?, status = 'Completed' WHERE id = ?")
        ->execute([$now, $dur, $id]);
    deductSessionIfNeeded($id);
}

redirect('/student/history.php?toast=' . urlencode('Checked out successfully.'));
